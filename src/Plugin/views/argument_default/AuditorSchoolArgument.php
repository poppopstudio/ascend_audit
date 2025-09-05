<?php

namespace Drupal\ascend_audit\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\Attribute\ViewsArgumentDefault;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;

/**
 * TODO: class docs.
 */
#[ViewsArgumentDefault(
  id: 'ascend_audit_auditor_school_argument',
  title: new TranslatableMarkup('Auditor School Argument'),
  // (optional) The short title used in the views UI.
  short_title: new TranslatableMarkup('OPTIONAL: replace this with a value'),
  // (optional) Whether the plugin should be not selectable in the UI. If it's
  // set to TRUE, you can still use it via the API in config files.
  no_ui: FALSE,
)]
class AuditorSchoolArgument extends ArgumentDefaultPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // $user =  \Drupal::currentUser();
    // return \Drupal::currentUser()->id();

    // Return working school ID for current user.
    return '14';
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
      'user',
      // school too? and/or profile!
    ];
  }

}
