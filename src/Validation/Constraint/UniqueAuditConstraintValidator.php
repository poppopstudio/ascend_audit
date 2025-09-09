<?php

namespace Drupal\ascend_audit\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Validates the UniqueAudit constraint.
 */
class UniqueAuditConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new UniqueAuditConstraintValidator instance.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }

    // Get the field values.
    $category_id = $entity->get('category')->target_id;
    $school_id = $entity->get('school')->target_id;
    $year = $entity->get('year')->value;

    // Skip validation if any required field is empty.
    if (!$category_id || !$school_id || !$year) {
      return;
    }

    // Query for existing audits with the same combination.
    $query = $this->entityTypeManager->getStorage('audit')->getQuery()
      ->condition('category', $category_id)
      ->condition('school', $school_id)
      ->condition('year', $year)
      ->accessCheck(FALSE);

    // If this is an existing entity (update), exclude it from the query.
    if (!$entity->isNew()) {
      $query->condition('id', $entity->id(), '<>');
    }

    $existing_audits = $query->execute();

    // If we found existing audits, add a violation.
    if (!empty($existing_audits)) {
      $this->context->addViolation($constraint->item_preexists);
    }
  }
}
