<?php

namespace Drupal\ascend_audit\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\field\Entity\FieldConfig;

/**
 * Contains hook implementations for the Ascend audit module.
 */
class AuditHooks {

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    return;
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('config_pages_insert')]
  public function configPagesInsert(EntityInterface $entity) {
    if ($entity->bundle() === 'ascend_settings') {
      $this->setFocusAreas($entity);
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('config_pages_update')]
  public function configPagesUpdate(EntityInterface $entity) {
    if ($entity->bundle() === 'ascend_settings') {
      $this->setFocusAreas($entity);
    }
  }

  protected function setFocusAreas(EntityInterface $entity) {
    // Get the ID of the term as set in the config_pages.
    $term_id = (int) $entity->ascend_focus_parent->first()->getValue()['target_id'];

    if (isset($term_id)) {
      $display_repository = \Drupal::service('entity_display.repository');
      $display = $display_repository->getFormDisplay('audit', 'audit', 'default');
      $options = $display->getComponent('category');
      $options['settings']['parent'] = $term_id;
      $display->setComponent('category', $options);
      $display->save();
    }
  }

}
