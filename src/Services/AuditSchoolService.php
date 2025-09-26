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
      return null;
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
      // Probably need error handling here?
    }

    return;
  }

  /**
   * Get working school in name format.
   */
  public function getWorkingSchoolName() {
    // Given we can get the ID from the other service function...
    $school_id = $this->getWorkingSchool();

    // We can get the school name from here.
    $school_entity = \Drupal::entityTypeManager()
      ->getStorage('school')
      ->load($school_id);

    return $school_entity->label();
  }

}
