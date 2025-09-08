<?php

namespace Drupal\ascend_audit\Services;

class AuditYearService {

  /**
   * Get working academic year in YY format.
   */
  public function getWorkingYear() {
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

  /**
   * Get a properly formatted YYYY/YY text version of the data.
   */
  public function getFormattedWorkingYear() {
    $yy = $this->getWorkingYear(); // Gets YY format (e.g., "24")

    // Convert YY back to full year for the start year.
    $start_year = 2000 + (int) $yy; // e.g., 2024

    // The end year is always start year + 1.
    $end_year = $start_year + 1; // e.g., 2025

    // Format as YYYY/YY.
    return $start_year . '/' . sprintf('%02d', $end_year % 100);
  }
}
