<?php

/**
 * @file
 * Provide token hooks for audit.
 */

use Drupal\ascend_audit\Hook\AuditHooks;
use Drupal\Core\Hook\LegacyHook;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Legacy hook implementation.
 *
 * @todo Remove this method when support for Drupal core < 11.1 is dropped.
 */
#[LegacyHook]
function ascend_audit_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  \Drupal::service(AuditHooks::class)->tokens($type, $tokens, $data, $options, $bubbleable_metadata);
}

/**
 * Legacy hook implementation.
 *
 * @todo Remove this method when support for Drupal core < 11.1 is dropped.
 */
#[LegacyHook]
function ascend_audit_token_info() {
  \Drupal::service(AuditHooks::class)->tokenInfo();
}
