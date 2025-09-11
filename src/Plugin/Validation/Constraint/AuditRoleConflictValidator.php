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
    if (in_array('auditor', $roles) && in_array('adviser', $roles)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
