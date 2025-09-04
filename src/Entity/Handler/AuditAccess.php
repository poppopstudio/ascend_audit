<?php

namespace Drupal\ascend_audit\Entity\Handler;

use Drupal\entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

class AuditAccess extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    // Only customize 'view' operation to enable "view own" logic.
    if ($operation === 'view') {
      // Check if user can view any entity.
      if ($account->hasPermission('view audits')) {
        return AccessResult::allowed()->cachePerPermissions();
      }

      // Check if user can view their own entities.
      if ($account->hasPermission('view own audits')) {
        return AccessResult::allowedIf($account->id() == $entity->getOwnerId())
          ->cachePerPermissions()
          ->cachePerUser()
          ->addCacheableDependency($entity);
      }

      return AccessResult::forbidden()->cachePerPermissions();
    }

    // For all other operations, use parent EntityAccessControlHandler logic.
    return parent::checkAccess($entity, $operation, $account);
  }
}
