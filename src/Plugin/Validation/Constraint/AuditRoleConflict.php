<?php

namespace Drupal\ascend_audit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validates that a user doesn't have conflicting audit roles.
 *
 * @Constraint(
 *   id = "AuditRoleConflict",
 *   label = @Translation("Audit Role Conflict", context = "Validation"),
 *   type = "entity"
 * )
 */
class AuditRoleConflict extends Constraint {
  /**
   * The error message.
   *
   * @var string
   */
  public $auditor_adviser_conflict = 'A user cannot have both Auditor and Adviser roles assigned simultaneously.';
  public $audit_manager_adviser_conflict = 'A user cannot have both Audit manager and Adviser roles assigned simultaneously.';
  public $audit_manager_auditor_conflict = 'A user cannot have both Audit manager and Auditor roles assigned simultaneously.';

}
