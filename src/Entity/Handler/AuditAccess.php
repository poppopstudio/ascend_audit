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

    // Check operations that require school-based access control
    if (in_array($operation, ['view', 'update'])) {

      // Global permissions first.
      if ($account->hasPermission($operation . ' audit')) {
        return AccessResult::allowed()->cachePerPermissions();
      }

      // Check if user is an auditor.
      if (in_array('auditor', $account->getRoles())) {

        // Check (below) if user has working access to the school.
        $auditor_linked = $this->checkAuditorSchoolLink($entity, $account);

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

  /**
   * Check if auditor is linked to the school on the audit entity.
   */
  protected function checkAuditorSchoolLink(EntityInterface $entity, AccountInterface $account): bool {

    // Get the school ID from the audit entity.
    $audit_school_id = $entity->get('school')->target_id;

    if (!$audit_school_id) {
      return FALSE; // No school set on audit, deny access.
    }

    // Load the audit's school.
    $audit_school = $entity->get('school')->entity;

    if (empty($audit_school)) {
      return FALSE; // No school set, deny access.
    }

    $school_auditor = $audit_school->get('ascend_sch_auditor')->target_id;
    return ($school_auditor == $account->id());
  }
}
