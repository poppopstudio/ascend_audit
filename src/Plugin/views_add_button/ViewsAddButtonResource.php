<?php

namespace Drupal\ascend_resource\Plugin\views_add_button;

use Drupal\Core\Url;
use Drupal\views_add_button\Plugin\views_add_button\ViewsAddButtonDefault;

/**
 * TODO: class docs.
 *
 * @ViewsAddButton(
 *   id = "ascend_resource_resource",
 *   label = @Translation("ViewsAddButtonResource"),
 *   category = @Translation("TODO: replace this with a value"),
 * )
 */
class ViewsAddButtonResource extends ViewsAddButtonDefault {

  /**
   * {@inheritdoc}
   */
  // public function description() {
  //   return $this->t('Default Views Add Button URL Generator for audit entitites');
  // }

  // /**
  //  * {@inheritdoc}
  //  */
  // public static function generateUrl($entity_type, $bundle, array $options, $context = '') {

  //   if (\Drupal::service('current_user')->hasPermission('use resource.default form mode')) {
  //     $route_name = 'entity.resource.add_form';
  //   }
  //   else {
  //     $route_name = 'entity.resource.add_form.simple';
  //   }

  //   return Url::fromRoute($route_name, $options);
  // }
}
