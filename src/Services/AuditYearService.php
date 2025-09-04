<?php

namespace Drupal\ascend_audit\Services;

class AuditYearService {

  /**
   * Get working school year in YY format.
   */
  public function getSchoolYear() {
    $current_date = new \DateTime();
    $current_year = (int) $current_date->format('y'); // Already YY format
    $current_month = (int) $current_date->format('n'); // 1-12

    // January-August, we're still in the previous school year.
    if ($current_month < 9) {
      $school_year = $current_year - 1;
    } else {
      // September-December, school year is the current year.
      $school_year = $current_year;
    }

    // Ensure it's always 2 digits with leading zero if needed.
    return sprintf('%02d', $school_year);
  }

}
