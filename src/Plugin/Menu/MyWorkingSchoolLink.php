<?php

declare(strict_types=1);

namespace Drupal\ascend_audit\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\StaticMenuLinkOverridesInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A menu link to update profiles based on user role.
 */
class MyWorkingSchoolLink extends MenuLinkDefault {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new MyWorkingSchoolLink.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\StaticMenuLinkOverridesInterface $static_override
   *   The static override storage.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    StaticMenuLinkOverridesInterface $static_override,
    AccountInterface $current_user
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $static_override);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu_link.static.overrides'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getUrlObject($title_attribute = TRUE) {
    $uid = $this->currentUser->id();
    $roles = $this->currentUser->getRoles(TRUE);

    // We asserted at user update that users cannot have both these roles.

    // Check for auditor role.
    if (in_array('auditor', $roles)) {
      return Url::fromUserInput('/user/' . $uid . '/auditor');
    }

    // Check for adviser role.
    if (in_array('adviser', $roles)) {
      return Url::fromUserInput('/user/' . $uid . '/adviser');
    }

    // Fallback to user profile (though this shouldn't happen).
    return Url::fromRoute('entity.user.canonical', ['user' => $uid]);
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    $roles = $this->currentUser->getRoles(TRUE);
    // Only show if user has auditor or adviser role.
    return in_array('auditor', $roles) || in_array('adviser', $roles);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return array_merge(parent::getCacheContexts(), ['user.roles']);
  }
}
