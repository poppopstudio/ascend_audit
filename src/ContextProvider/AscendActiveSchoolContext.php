<?php

declare(strict_types=1);

namespace Drupal\ascend_audit\ContextProvider;

use Drupal\ascend_audit\Services\AuditSchoolService;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Provides the 'ascend_active_school' context.
 */
class AscendActiveSchoolContext implements CacheContextInterface {

  /**
   * Creates a AscendActiveSchool instance.
   *
   * @param \Drupal\ascend_audit\Services\AuditSchoolService $auditSchoolService
   *   The audit school service.
   */
  public function __construct(
    protected AuditSchoolService $auditSchoolService,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Active school');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->auditSchoolService->getWorkingSchool();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    $cache_metadata = new CacheableMetadata();

    // Depend on the school.
    $cache_metadata->addCacheTags($this->auditSchoolService->getWorkingSchoolEntity()->getCacheTags());

    return $cache_metadata;
  }

}
