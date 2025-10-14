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
