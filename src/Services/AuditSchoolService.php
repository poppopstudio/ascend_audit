<?php

namespace Drupal\ascend_audit\Services;

class AuditSchoolService {

  /**
   * Get working school in entity ID format.
   */
  public function getWorkingSchool() {
    $current_user = \Drupal::currentUser();
    $current_user_roles = array_values($current_user->getRoles(TRUE));

    /**
     * If we need to have profiles for non-auditor users, here is currently
     * the best place to put checks for that.
     */

    // Only auditors have profiles atm.
    if (in_array('auditor', $current_user_roles)) {

      /** @var \Drupal\profile\entity\Profile */
      $auditor_profile = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByUser($current_user, 'auditor');

      // Return the working school ID from the profile.
      return $auditor_profile->get('ascend_p_school')->target_id;
    }

    if (in_array('adviser', $current_user_roles)) {

      /** @var \Drupal\profile\entity\Profile */
      $auditor_profile = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByUser($current_user, 'adviser');

      // Return the working school ID from the profile.
      return $auditor_profile->get('ascend_p_school')->target_id;
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
