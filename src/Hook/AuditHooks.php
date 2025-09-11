<?php

namespace Drupal\ascend_audit\Hook;

use Drupal\ascend_audit\Services\AuditYearService;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountInterface;

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
  public function userAlter(array &$entity_types) {
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
    }
  }


  /**
   * Implements hook_form_FORM_ID_alter().
   */
  // #[Hook('form_user_validate')]
  // function formUserValidate(&$form, FormStateInterface $form_state) {
  //   // Get the user entity from the form state.
  //   $a = 1;
  //   $user = $form_state->getFormObject()->getEntity();

  //   if ($user->hasRole('adviser') && $user->hasRole('auditor')) {
  //     // Set a form error message. This will prevent the form from saving.
  //     $form_state->setErrorByName('roles', t('A user cannot be both an Auditor and an Adviser.'));
  //   }
  // }


  /**
   * Implements hook_token_info().
   */
  // #[Hook('token_info')]
  // public function tokenInfo() {
  //   $types['audit'] = [
  //     'name' => t('Audit'),
  //     'description' => t('Audit-related tokens.'),
  //   ];

  //   $tokens['school_year'] = [
  //     'name' => t('School Year (YY)'),
  //     'description' => t('Current school year in YY format (e.g. 24).'),
  //   ];

  //   return [
  //     'types' => $types,
  //     'tokens' => ['audit' => $tokens],
  //   ];
  // }

  /**
   * Implements hook_tokens().
   */
  // public function tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  //   $replacements = [];

  //   if ($type == 'audit') {
  //     $year = \Drupal::service(AuditYearService::class)->getWorkingYear();

  //     foreach ($tokens as $name => $original) {
  //       switch ($name) {
  //         case 'school_year':
  //           $replacements[$original] = $year;
  //           break;
  //       }
  //     }
  //   }

  //   return $replacements;
  // }

}
