<?php

namespace Drupal\ascend_audit\Entity\Handler;

use Drupal\ascend_audit\Entity\AuditInterface;
use Drupal\ascend_ap\Entity\ApInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a method to check if an auditor is linked to a school.
 */
trait AuditorSchoolLinkTrait {
  /**
   * Check if auditor is linked to the school on the entity.
   * Will accept AP or Audit entity.
   *
   * @param \Drupal\ascend_ap\Entity\ApInterface|\Drupal\ascend_audit\Entity\AuditInterface $entity
   *   The entity to check (Audit or Ap).
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to check.
   *
   * @return bool
   *   TRUE if the auditor is linked to the school, FALSE otherwise.
   */
  protected function isAuditorSchoolLink(ApInterface|AuditInterface $entity, AccountInterface $account): bool {
    // Get the school ID from the AP/audit entity.
    $school_id = $entity->get('school')->target_id;

    if (!$school_id) {
      // No school set on entity, deny access.
      return FALSE;
    }

    // Load the entity's school.
    $school = $entity->get('school')->entity;

    if (empty($school)) {
      // No school set, deny access.
      return FALSE;
    }

    // Check if the account is one of the auditors (multi-value field)
    $auditor_ids = array_column(
      $school->get('ascend_sch_auditor')->getValue(),
      'target_id'
    );
    return in_array($account->id(), $auditor_ids);
  }
}
