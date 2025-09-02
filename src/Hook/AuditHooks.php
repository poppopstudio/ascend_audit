<?php

namespace Drupal\ascend_audit\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

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
      $this->setFocusAreas();
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('config_pages_update')]
  public function configPagesUpdate(EntityInterface $entity) {
    if ($entity->bundle() === 'ascend_settings') {
      $this->setFocusAreas();
    }
  }

  protected function setFocusAreas() {
    $a = 1;
  }
}
