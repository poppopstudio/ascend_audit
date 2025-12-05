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
   * Implements hook_module_implements_alter().
   * NB This hook is required to support the presave hook below!
   */
  #[Hook('module_implements_alter')]
  public function moduleImplementsAlter(&$implementations, $hook) {
    if ($hook === 'user_presave') {
      // Move our implementation to the end.
      $group = $implementations['ascend_audit'];
      unset($implementations['ascend_audit']);
      $implementations['ascend_audit'] = $group;
    }
  }

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

    // Remove school from auditor profiles when auditor removed from school.

    $old_auditors = $entity->original->get('ascend_sch_auditor')->referencedEntities();

    // Nothing to do if no auditors were originally set.
    if (empty($old_auditors)) {
      return;
    }

    $new_auditor_ids = array_column(
      $entity->get('ascend_sch_auditor')->getValue(),
      'target_id'
    );

    foreach ($old_auditors as $old_auditor) {
      if (!in_array($old_auditor->id(), $new_auditor_ids)) {

        // Auditor removed, remove school from their 'auditor' profile & save.
        /** @var \Drupal\profile\entity\Profile */
        $profile = \Drupal::entityTypeManager()
          ->getStorage('profile')
          ->loadByUser($old_auditor, 'auditor');

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

    $target_types = ['ap', 'audit'];

    // Rewrite the ugly out of the AP/audit titles (swap ref for category).
    foreach ($target_types as $type) {
      if ($route_match->getRouteName() == "entity.$type.canonical") {
        // Get the entity object.
        $entity = $route_match->getParameter($type);

        // Entity has a non-empty category variable?
        if ($entity && $entity->hasField('category') && !$entity->get('category')->isEmpty()) {
          $category = $entity->get('category')->entity;
          if ($category) {
            // Set a sensible title instead of the garbage one.
            $entity_label = \Drupal::entityTypeManager()->getDefinition($type)->getLabel();
            $variables['ref'] = $variables['title'];
            $variables['title'] = $entity_label . ": " . $category->label();
          }
        }
      }
    }
  }

}
