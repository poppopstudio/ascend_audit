<?php

namespace Drupal\ascend_audit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the academic year formatter.
 *
 * @FieldFormatter(
 *   id = "ascend_audit_year_formatter",
 *   label = @Translation("Academic year"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class AuditYearFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $yy = $item->value;

      if ($yy === NULL || $yy === '') {
        continue;
      }

      // Convert YY back to full year for the start year.
      $start_year = 2000 + (int) $yy; // e.g., 2024

      // The end year is always start year + 1.
      $end_year = $start_year + 1; // e.g., 2025

      // Format as YYYY/YY.
      $formatted_year = $start_year . '/' . sprintf('%02d', $end_year % 100);

      $elements[$delta] = [
        '#markup' => $formatted_year,
      ];
    }

    return $elements;
  }
}
