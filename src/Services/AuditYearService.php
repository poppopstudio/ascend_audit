<?php

namespace Drupal\ascend_audit\Services;

class AuditYearService {

  /**
   * Get working school year in YY format.
   */
  public function getSchoolYear() {
    return date('y'); // Returns current year as YY (e.g., "24" for 2024)
  }

}
