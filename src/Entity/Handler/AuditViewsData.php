<?php

namespace Drupal\ascend_audit\Entity\Handler;

use Drupal\views\EntityViewsData;

/**
 * Provides the Views data handler for the Resource entity.
 */
class AuditViewsData extends EntityViewsData {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Fetch a computed value for the title/label/whatever.
    $data['audit']['computed_label'] = [
      'title' => $this->t('Audit Label'),
      'help' => $this->t('The computed label (ai:cX.sX.yX)'),
      'field' => [
        'id' => 'audit_computed_label',
      ],
    ];

    // Add the filter for "Audit has category".
    // $data[BASE TABLE of field][TERM FIELD column id]
    $data['audit']['category']['filter'] = [
      'title' => $this->t('Audit item has category'),
      'id' => 'taxonomy_index_tid',
      'field' => 'category',
      'numeric' => TRUE,
      'allow empty' => TRUE,
    ];

    // Add the relationship for "Audit has category".
    $data['audit']['category']['relationship'] = [
      'title' => $this->t('Audit item has category'),
      'help' => $this->t('Category referenced by audit item.'),
      'id' => 'standard',
      'base' => 'taxonomy_term_field_data',
      'base field' => 'tid',
      'field' => 'category',
      'label' => $this->t('Category'),
    ];

    return $data;
  }

}
