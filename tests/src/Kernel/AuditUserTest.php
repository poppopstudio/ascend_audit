<?php

declare(strict_types=1);

namespace Drupal\Tests\ascend_audit\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\EntityTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests users with audit access.
 *
 * @group ascend_audit
 */
class AuditUserTest extends KernelTestBase {

  use UserCreationTrait;
  use EntityTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'ascend_audit',
  ];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    // Create dummy versions of the roles for the test.
    // Roles we assign.
    $this->createRole([], 'adviser');
    $this->createRole([], 'site_manager');

    // Roles that get assigned additionally.
    $this->createRole([], 'resource_manager');
    $this->createRole([], 'content_editor');
    $this->createRole([], 'user_manager');
    $this->createRole([], 'audit_manager');
  }

  /**
   * Tests users get additional roles when granted audit roles.
   */
  public function testAdditionalRoles(): void {
    // Set up the current user, as the hook we are testing checks for this.
    $this->setUpCurrentUser(permissions: [
      'administer users',
    ]);

    // Test with a user created with the adviser role.
    $new_user = $this->createUser(values: [
      'roles' => [
        'adviser',
      ]
    ]);

    $this->assertEqualsCanonicalizing(
      [
        'authenticated',
        'adviser',
        'resource_manager',
      ],
      $new_user->getRoles(),
      'A user created with the adviser role gets the extra role.',
    );

    // Test with a user granted the adviser role later.
    $existing_user = $this->createUser();
    $existing_user->addRole('adviser');
    $existing_user->save();

    $this->assertEqualsCanonicalizing(
      [
        'authenticated',
        'adviser',
        'resource_manager',
      ],
      $new_user->getRoles(),
      'A user created with the adviser role gets the extra role.',
    );

    // Test with a user created with the site_manager role.
    $new_user = $this->createUser(values: [
      'roles' => [
        'site_manager',
      ]
    ]);

    $this->assertEqualsCanonicalizing(
      [
        'authenticated',
        'site_manager',
        'resource_manager',
        'content_editor',
        'user_manager',
        'audit_manager',
      ],
      $new_user->getRoles(),
      'A user created with the site_manager role gets the extra roles.',
    );

    // Test with a user granted the adviser role later.
    $existing_user = $this->createUser();
    $existing_user->addRole('site_manager');
    $existing_user->save();

    $this->assertEqualsCanonicalizing(
      [
        'authenticated',
        'site_manager',
        'resource_manager',
        'content_editor',
        'user_manager',
        'audit_manager',
      ],
      $new_user->getRoles(),
      'A user created with the site_manager role gets the extra roles.',
    );
  }

}
