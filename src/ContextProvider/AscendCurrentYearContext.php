<?php

declare(strict_types=1);

namespace Drupal\ascend_audit\ContextProvider;

use Drupal\ascend_audit\Services\AuditYearService;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Provides the 'ascend_current_year' context.
 */
class AscendCurrentYearContext implements CacheContextInterface {

  /**
   * Creates a AscendCurrentYear instance.
   *
   * @param \Drupal\ascend_audit\Services\AuditYearService $audit_year_service
   *   The audit year service.
   */
  public function __construct(
    protected AuditYearService $audit_year_service,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Current audit year');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->audit_year_service->getWorkingYear();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    $cache_metadata = new CacheableMetadata();
    $cache_metadata->setCacheMaxAge($this->audit_year_service->getWorkingYearCacheExpiry());
    return $cache_metadata;
  }

}
