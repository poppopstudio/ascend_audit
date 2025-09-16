<?php

namespace Drupal\ascend_audit\Plugin\views_add_button;

use Drupal\Core\Url;
use Drupal\views_add_button\Plugin\views_add_button\ViewsAddButtonDefault;

/**
 * @ViewsAddButton(
 *   id = "ascend_audit_audit",
 *   label = @Translation("Audit"),
 *   category = @Translation("Views add button: Add audit"),
 * )
 */
class ViewsAddButtonAudit extends ViewsAddButtonDefault {

  /**
   * {@inheritdoc}
   */
  public function description() {
    return $this->t('Views Add Button URL Generator for audit items');
  }

  /**
   * {@inheritdoc}
   */
  public static function generateUrl($entity_type, $bundle, array $options, $context = '') {

    if (\Drupal::service('current_user')->hasPermission('use audit.default form mode')) {
      $route_name = 'entity.audit.add_form';
    }
    else {
      $route_name = 'entity.audit.add_form.auditor';
    }

    return Url::fromRoute($route_name, [], $options);
  }
}
