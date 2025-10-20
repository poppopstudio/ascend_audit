<?php

declare(strict_types=1);

namespace Drupal\ascend_audit\Hook;

use Drupal\Core\Entity\EntityInterface;
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

      if (!$original) {
        \Drupal::logger('ascend_audit')->info('Additional role(s) assigned to new user @user: @additional', $message_vars);
        \Drupal::messenger()->addMessage(t('Additional role(s) assigned to @user: @additional', $message_vars));
      }
      else {
        $message_vars['@primary'] = implode(', ', $new_roles);
        \Drupal::logger('ascend_audit')->info('Additional role(s) assigned to @user after @primary assignment: @additional', $message_vars);
        \Drupal::messenger()->addMessage(t('Additional role(s) assigned to @user after @primary assignment: <strong>@additional</strong>', $message_vars));
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

}
