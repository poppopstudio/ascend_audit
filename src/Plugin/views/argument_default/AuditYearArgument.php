<?php

namespace Drupal\ascend_audit\Plugin\views\argument_default;

use Drupal\ascend_audit\Services\AuditYearService;
use Drupal\Core\Cache\Cache;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsArgumentDefault;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * TODO: class docs.
 */
#[ViewsArgumentDefault(
  id: 'ascend_audit_audit_year_argument',
  title: new TranslatableMarkup('Audit Year Argument'),
  short_title: new TranslatableMarkup('Audit year argument'),
  no_ui: FALSE,
)]
class AuditYearArgument extends ArgumentDefaultPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return \Drupal::service(AuditYearService::class)->getWorkingYear();
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
      'user', // surely not, here.
    ];
  }

}
