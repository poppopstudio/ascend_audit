<?php

namespace Drupal\ascend_audit\Entity\Handler;

use Drupal\ascend_audit\Entity\Handler\AuditorSchoolLinkTrait;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
class AuditAccess extends EntityAccessControlHandler {

  use AuditorSchoolLinkTrait;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Handle revision operations.
    if (in_array($operation, ['view revision', 'view all revisions'])) {
      return AccessResult::allowedIfHasPermission($account, 'view audit revisions')
        ->cachePerPermissions();
    }

    if (in_array($operation, ['revert', 'revert revision'])) {
      return AccessResult::allowedIfHasPermission($account, 'revert audit revisions')
        ->cachePerPermissions();
    }

    if ($operation === 'delete revision') {
      return AccessResult::allowedIfHasPermission($account, 'delete audit revisions')
        ->cachePerPermissions();
    }

    // Check operations that require school-based access control
    if (in_array($operation, ['view', 'update'])) {

      // Global permissions first.
      if ($account->hasPermission($operation . ' audit')) {
        return AccessResult::allowed()->cachePerPermissions();
      }

      // Check if user is an auditor.
      if (in_array('auditor', $account->getRoles())) {

        // Check (below) if user has working access to the school.
        $auditor_linked = $this->isAuditorSchoolLink($entity, $account);

        if (!$auditor_linked) {
          // If the auditor is linked, we know the school exists.
          $audit_school = $entity->get('school')->entity;

          return AccessResult::forbidden()
            ->cachePerPermissions()
            ->cachePerUser()
            ->addCacheableDependency($entity)
            // Add a dependency on the school, as if that is edited the auditor
            // could have changed.
            ->addCacheableDependency($audit_school);
        }

        // For view operation, check "view own" permission.
        if ($operation === 'view' && $account->hasPermission('view own audit')) {
          return AccessResult::allowed()
            ->cachePerPermissions()
            ->cachePerUser()
            ->addCacheableDependency($entity);
        }

        // For update operation, check "update own" permission.
        if ($operation === 'update' && $account->hasPermission('update own audit')) {
          return AccessResult::allowed()
            ->cachePerPermissions()
            ->cachePerUser()
            ->addCacheableDependency($entity);
        }
      }

      return AccessResult::forbidden()->cachePerPermissions();
    }

    // For all other operations, use parent EntityAccessControlHandler logic
    return parent::checkAccess($entity, $operation, $account);
  }

}
