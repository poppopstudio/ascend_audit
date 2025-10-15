<?php

namespace Drupal\ascend_audit\Plugin\views\argument_default;

use Drupal\ascend_audit\Services\AuditSchoolService;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsArgumentDefault;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * Default argument provider for the current user's working school.
 */
#[ViewsArgumentDefault(
  id: 'ascend_audit_auditor_school_argument',
  title: new TranslatableMarkup('Auditor School Argument'),
  short_title: new TranslatableMarkup('Auditor School Argument'),
  no_ui: FALSE,
)]
class AuditorSchoolArgument extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return \Drupal::service(AuditSchoolService::class)->getWorkingSchool();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'ascend_active_school',
    ];
  }

}
