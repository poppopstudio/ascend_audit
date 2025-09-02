<?php

namespace Drupal\ascend_audit\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for Audit entities.
 */
interface AuditInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

}
