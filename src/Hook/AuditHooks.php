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

  /**
   * Implements hook_user_update().
   */
  #[Hook('user_update')]
  function userUpdate(UserInterface $account) {

    $current_user = \Drupal::currentUser();

    // Only issue these role updates if current user has user admin perms.
    if (!$current_user->hasPermission('administer users')) {
      return;
    }

    $original = $account->original ?? NULL;

    if (!$original) {
      return;
    }

    // Get current and original roles (excl. anon/auth).
    $current_roles = $account->getRoles(TRUE);
    $original_roles = $original->getRoles(TRUE);

    // Find newly added roles.
    $new_roles = array_diff($current_roles, $original_roles);

    if (empty($new_roles)) {
      return;
    }

    // Define additional roles for primary roles.
    $role_mappings = [
      'site_manager' => ['content_editor', 'user_manager', 'resource_manager', 'audit_manager'],
      'adviser' => ['resource_manager'],
    ];

    $roles_to_add = [];

    foreach ($new_roles as $new_role) {
      if (isset($role_mappings[$new_role])) {
        foreach ($role_mappings[$new_role] as $additional_role) {
          if (!$account->hasRole($additional_role)) {
            $roles_to_add[] = $additional_role;
          }
        }
      }
    }

    // Add additional roles if any were found.
    if (!empty($roles_to_add)) {
      foreach ($roles_to_add as $role) {
        $account->addRole($role);
      }

      $account->save();

      \Drupal::logger('ascend_audit')->info('Additional role(s) assigned to @user after @primary assignment: @additional', [
        '@user' => $account->getDisplayName(),
        '@primary' => implode(', ', $new_roles),
        '@additional' => implode(', ', $roles_to_add),
      ]);

      \Drupal::messenger()->addMessage(t('Additional role(s) assigned to @user after @primary assignment: <strong>@additional</strong>', [
        '@user' => $account->getDisplayName(),
        '@primary' => implode(', ', $new_roles),
        '@additional' => implode(', ', $roles_to_add),
      ]));
    }
  }


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
