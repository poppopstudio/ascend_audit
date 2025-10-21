<?php

declare(strict_types=1);

namespace Drupal\ascend_audit\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Contains entity hook implementations for the Ascend audit module.
 */
class EntityHooks {

  /**
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('user_presave')]
  public function userPresave(EntityInterface $entity) {
    // Assign additional roles to users when certain roles are assigned to them.
    /** @var \Drupal\user\UserInterface $account */
    $account = $entity;

    $current_roles = $account->getRoles(TRUE);

    $original = $account->original ?? NULL;
    if ($original) {
      $original_roles = $original->getRoles(TRUE);
    }
    else {
      $original_roles = [];
    }

    // Find newly added roles.
    $new_roles = array_diff($current_roles, $original_roles);

    if (empty($new_roles)) {
      return;
    }

    $current_user = \Drupal::currentUser();

    // Only issue these role updates if current user has user admin perms.
    if (!$current_user->hasPermission('administer users')) {
      return;
    }

    // Define additional roles for primary roles.
    $role_mappings = [
      'adviser' => ['resource_manager'],
      'site_manager' => ['content_editor', 'user_manager', 'resource_manager', 'audit_manager'],
    ];

    $roles_to_add = [];

    foreach ($new_roles as $role) {
      if (isset($role_mappings[$role])) {
        foreach ($role_mappings[$role] as $additional_role) {
          if (!$account->hasRole($additional_role)) {
            $roles_to_add[] = $additional_role;
          }
        }
      }
    }

    // Add additional roles if any were found.
    if (!empty($roles_to_add)) {
      foreach ($roles_to_add as $role) {
        $account->addRole($role);
      }

      $message_vars = [
        '@user' => $account->getDisplayName(),
        '@additional' => implode(', ', $roles_to_add),
      ];

      if ($original) {
        $message_vars['@primary'] = implode(', ', $new_roles);
        \Drupal::logger('ascend_audit')->info('Additional role(s) assigned to @user after @primary assignment: @additional', $message_vars);
        \Drupal::messenger()->addMessage(t('Additional role(s) assigned to @user after @primary assignment: <strong>@additional</strong>', $message_vars));
      }
      else {
        \Drupal::logger('ascend_audit')->info('Additional role(s) assigned to new user @user: @additional', $message_vars);
        \Drupal::messenger()->addMessage(t('Additional role(s) assigned to @user: @additional', $message_vars));
      }
    }
  }


  /**
   * Implements hook_ENTITY_TYPE_update().
   */
  #[Hook('school_update')]
  public function schoolUpdate(EntityInterface $entity) {
    // When the auditor for a school is changed, remove that school from the
    // auditor's profile.
    $new_auditor_id = $entity->ascend_sch_auditor->target_id;
    $old_auditor_id = $entity->original->ascend_sch_auditor->target_id;

    // Nothing to do if the school's auditor is unchanged.
    if ($new_auditor_id == $old_auditor_id) {
      return;
    }

    // Nothing to do if the previous value was empty, as an auditor is not being
    // removed from the school.
    if (empty($old_auditor_id)) {
      return;
    }

    $old_auditor = $entity->original->ascend_sch_auditor->entity;

    // Load any profiles belonging to the old auditor.
    $profile_types = ['auditor', 'adviser'];
    foreach ($profile_types as $profile_type_id) {
      /** @var \Drupal\profile\entity\Profile */
      $profile = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByUser($old_auditor, $profile_type_id);

      // Skip if there is no profile.
      if (empty($profile)) {
        continue;
      }

      // Skip if the profile doesn't point to the school being edited.
      if ($profile->ascend_p_school->target_id != $entity->id()) {
        continue;
      }

      // Empty the value in the profile and save it.
      $profile->get('ascend_p_school')->set(0, NULL);
      $profile->save();
    }
  }


  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_user_cancel_form_alter')]
  public function formUserCancelAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Get the user being cancelled.
    $user = $form_state->getFormObject()->getEntity();

    // Check if user has any audit or ap content
    $has_content = $this->userHasContent($user->id());

    if ($has_content) {
      // Remove the destructive options.
      unset($form['user_cancel_method']['#options']['user_cancel_block_unpublish']);
      unset($form['user_cancel_method']['#options']['user_cancel_reassign']);
      unset($form['user_cancel_method']['#options']['user_cancel_delete']);

      \Drupal::messenger()->addWarning(t('Audit or action plan content exists which would be orphaned if the user were removed/blocked in a destructive fashion. Consequently, the set of available actions has been limited to maintain data integrity.'));
    }
  }

  /**
   * Check if a user has any audit or ap content.
   */
  protected function userHasContent($uid): bool {
    $entity_type_manager = \Drupal::entityTypeManager();

    // Check audit entities - more likely to hit, so put first.
    $audit_count = $entity_type_manager->getStorage('audit')->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', $uid)
      ->count()
      ->execute();

    if ($audit_count > 0) {
      return TRUE;
    }

    // Check ap entities.
    $ap_count = $entity_type_manager->getStorage('ap')->getQuery()
      ->accessCheck(FALSE)
      ->condition('uid', $uid)
      ->count()
      ->execute();

    return $ap_count > 0;
  }


  /**
   * Implements hook_preprocess_page_title().
   */
  #[Hook('preprocess_page_title')]
  public function preprocessPageTitle(&$variables) {
    $route_match = \Drupal::routeMatch();

    // Rewrite the ugly out of the AP title (swap ref for category)
    if ($route_match->getRouteName() == 'entity.ap.canonical') {
      $ap = $route_match->getParameter('ap');

      if ($ap && $ap->hasField('category') && !$ap->get('category')->isEmpty()) {
        $category = $ap->get('category')->entity;
        if ($category) {
          $variables['ref'] = $variables['title'];
          $variables['title'] = $category->label();
        }
      }
    }

    // Same for audit.
    if ($route_match->getRouteName() == 'entity.audit.canonical') {
      $audit = $route_match->getParameter('audit');

      if ($audit && $audit->hasField('category') && !$audit->get('category')->isEmpty()) {
        $category = $audit->get('category')->entity;
        if ($category) {
          $variables['title'] = $category->label();
        }
      }
    }
  }

}
