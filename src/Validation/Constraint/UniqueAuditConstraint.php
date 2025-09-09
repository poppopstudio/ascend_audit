<?php

namespace Drupal\ascend_audit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that an audit's category + school + year combination is unique.
 *
 * @Constraint(
 *   id = "UniqueAuditConstraint",
 *   label = @Translation("Unique audit", context = "Validation"),
 *   type = "entity"
 * )
 */
class UniqueAuditConstraint extends Constraint {

  /**
   * The message that will be shown if the combination is not unique.
   */
  public $item_preexists = 'An audit item already exists for this combination of category, school, and year.';
}
