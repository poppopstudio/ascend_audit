<?php

namespace Drupal\ascend_audit\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\user\UserInterface;

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
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) {
    if (isset($entity_types['user'])) {
      $entity_types['user']->addConstraint('AuditRoleConflict');
    }
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

  // Update ap/audit forms' category field settings to correct term id.
  protected function setFocusAreas(EntityInterface $entity) {

    // Get the ID of the term as set in the config_pages.
    $term_id = (int) $entity->ascend_focus_parent->first()->getValue()['target_id'];

    if (isset($term_id)) {
      // Get the form settings for the category field on audit.
      $display_repository = \Drupal::service('entity_display.repository');
      $display = $display_repository->getFormDisplay('audit', 'audit', 'default');

      // Get the category form component and update the parent setting.
      $options = $display->getComponent('category');
      $options['settings']['parent'] = $term_id;
      $display->setComponent('category', $options);
      $display->save();

      if (\Drupal::service('module_handler')->moduleExists('ascend_ap')) {
        // Get the form settings for the category field on APs.
        $display = $display_repository->getFormDisplay('ap', 'ap', 'default');

        // Get the category form component and update the parent setting.
        $options = $display->getComponent('category');
        $options['settings']['parent'] = $term_id;
        $display->setComponent('category', $options);
        $display->save();
      }
    }
  }

}
