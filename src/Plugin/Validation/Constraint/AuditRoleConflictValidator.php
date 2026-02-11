<?php

namespace Drupal\ascend_audit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the AuditRoleConflict constraint.
 */
class AuditRoleConflictValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    // Only validate User entities.
    if ($entity->getEntityTypeId() !== 'user') {
      return;
    }

    $roles = $entity->getRoles();

    // Check for conflicting roles.

    //    // Auditor/adviser cannot co-exist, privacy issue.
    //    if (in_array('auditor', $roles) && in_array('adviser', $roles)) {
    //      $this->context->addViolation($constraint->auditor_adviser_conflict);
    //    }
    // commented out on 11 Feb. This validation is not required atm.

    // Audit manager/adviser cannot co-exist, privacy issue.
    if (in_array('audit_manager', $roles) && in_array('adviser', $roles)) {
      $this->context->addViolation($constraint->audit_manager_adviser_conflict);
    }

    // Audit manager/auditor cannot co-exist, privacy issue.
    if (in_array('audit_manager', $roles) && in_array('auditor', $roles)) {
      $this->context->addViolation($constraint->audit_manager_auditor_conflict);
    }
  }

}
