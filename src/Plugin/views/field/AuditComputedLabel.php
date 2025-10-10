<?php

namespace Drupal\ascend_audit\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler for computed Audit item label.
 *
 * @ViewsField("audit_computed_label")
 */
class AuditComputedLabel extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // No query needed since we compute on the fly.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    return ['#markup' => $entity->label()];
  }
}
