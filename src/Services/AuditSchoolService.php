<?php

namespace Drupal\ascend_audit\Services;

class AuditSchoolService {

  /**
   * Get working school in entity ID format.
   */
  public function getWorkingSchool() {
    $current_user = \Drupal::currentUser();
    $current_user_roles = array_values($current_user->getRoles(TRUE));

    // Define the roles that have profile-based school assignments.
    $profile_roles = ['auditor', 'adviser'];

    // If any profile role is present, return a working school ID.

    // Get roles the user actually has from our target roles.
    $matching_roles = array_intersect($profile_roles, $current_user_roles);
    if (empty($matching_roles)) {
      /**
       * Could do something a bit more impactful but user shouldn't have perms
       * to access anything that requires the value?
       */
      return 1; // was null but...
    }

    // Process matching roles (maintains priority order from $profile_roles).
    foreach ($matching_roles as $role) {

      /** @var \Drupal\profile\entity\Profile */
      $profile = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByUser($current_user, $role);

      if ($profile && !$profile->get('ascend_p_school')->isEmpty()) {
        return $profile->get('ascend_p_school')->target_id;
      }
    }

    // At this point, user does not have a saved profile entry.
    $message_vars = [
      '@user' => $current_user->getDisplayName(),
    ];

    \Drupal::logger('ascend_audit')->warning('User @user does not have a working school assigned in their profile', $message_vars);
    \Drupal::messenger()->addWarning(t('Please set a working school in your profile to continue.', $message_vars));

    // Return at least a token value in order to not break things.
    return 1; // Will be the ID of the first available school.
  }

  /**
   * Get working school in name format.
   */
  public function getWorkingSchoolName() {
    // Given we can get the ID from the other service function...
    $school_id = $this->getWorkingSchool();

    if (!isset($school_id) || !is_integer($school_id)) {
      return;
    }

    // We can get the school name from here.
    $school_entity = \Drupal::entityTypeManager()
      ->getStorage('school')
      ->load($school_id);

    if (!isset($school_entity)) {
      return;
    }

    return $school_entity->label();
  }
}
