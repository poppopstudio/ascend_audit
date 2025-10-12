<?php

namespace Drupal\ascend_audit\Plugin\pathauto\AliasType;

use Drupal\pathauto\Plugin\pathauto\AliasType\EntityAliasTypeBase;

/**
 * Provides an alias type for Audit entities.
 *
 * @AliasType(
 *   id = "audit",
 *   label = @Translation("Audit item"),
 *   types = {"audit"},
 *   provider = "ascend_audit",
 *   context_definitions = {
 *     "audit" = @ContextDefinition("entity:audit", label = @Translation("Audit item"))
 *   }
 * )
 */
class AuditAliasType extends EntityAliasTypeBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'audit';
  }
}
