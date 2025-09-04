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
  // (optional) The short title used in the views UI.
  short_title: new TranslatableMarkup('OPTIONAL: replace this with a value'),
  // (optional) Whether the plugin should be not selectable in the UI. If it's
  // set to TRUE, you can still use it via the API in config files.
  no_ui: FALSE,
)]
class AuditYearArgument extends ArgumentDefaultPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    return \Drupal::service(AuditYearService::class)->getSchoolYear();
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
