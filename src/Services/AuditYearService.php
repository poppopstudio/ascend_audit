<?php

namespace Drupal\ascend_audit\Services;

use DateTime;
use DateTimeInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Gets the current academic year.
 */
class AuditYearService {

  /**
   * Creates a AscendCurrentYear instance.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(
    protected TimeInterface $time,
  ) {
  }

  /**
   * Get working academic year in YY format.
   *
   * @return int
   *   The 2-digit year of the start calendar year of the current academic year.
   *   For example, if the academic year is 2025-26, this returns 25.
   */
  public function getWorkingYear(): int {
    $current_date = new \DateTime();
    $current_year = (int) $current_date->format('y'); // Already YY format
    $current_month = (int) $current_date->format('n'); // 1-12

    // January-August, we're still in the previous school year.
    if ($current_month < 9) {
      $school_year = $current_year - 1;
    }
    else {
      // September-December, school year is the current year.
      $school_year = $current_year;
    }

    // Ensure it's always 2 digits with leading zero if needed.
    return sprintf('%02d', $school_year);
  }

  /**
   * Get a properly formatted YYYY/YY text version of the data.
   */
  public function getFormattedWorkingYear(): string {
    $yy = $this->getWorkingYear(); // Gets YY format (e.g., "24")

    // Convert YY back to full year for the start year.
    $start_year = 2000 + (int) $yy; // e.g., 2024

    // The end year is always start year + 1.
    $end_year = $start_year + 1; // e.g., 2025

    // Format as YYYY/YY.
    return $start_year . '/' . sprintf('%02d', $end_year % 100);
  }

  /**
   * Gets the end date of the current year.
   *
   * @return \DateTimeInterface
   */
  public function getWorkingYearEndDate(): DateTimeInterface {
    $yy = $this->getWorkingYear();
    $start_year = 2000 + (int) $yy;

    return new DateTime($start_year . '/08/31');
  }

  /**
   * Gets a cache expiry for the current year.
   *
   * This should be used as the cache expiry time for anything which depends on
   * the current for its output.
   *
   * @return integer
   *   The number of seconds between the request time and the end of the
   *   current year.
   */
  public function getWorkingYearCacheExpiry(): int {
    $working_year_end_date = $this->getWorkingYearEndDate();
    $working_year_end_timestamp = $working_year_end_date->getTimestamp();
    $current_timestamp = $this->time->getRequestTime();

    return ($working_year_end_timestamp - $current_timestamp);
  }

}
