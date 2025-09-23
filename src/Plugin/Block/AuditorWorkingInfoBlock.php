<?php

namespace Drupal\ascend_audit\Plugin\Block;

use Drupal\ascend_audit\Services\AuditSchoolService;
use Drupal\ascend_audit\Services\AuditYearService;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Provides an 'Auditor Working Info' Block.
 *
 * @Block(
 *   id = "auditor_working_info",
 *   admin_label = @Translation("Auditor Working Info"),
 *   category = @Translation("Ascend Audit"),
 * )
 */
class AuditorWorkingInfoBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The audit school service.
   *
   * @var \Drupal\ascend_audit\Services\AuditSchoolService
   */
  protected $auditSchoolService;

  /**
   * The audit year service.
   *
   * @var \Drupal\ascend_audit\Services\AuditYearService
   */
  protected $auditYearService;

  /**
   * Constructs a new AuditorWorkingInfoBlock instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountProxyInterface $current_user, AuditSchoolService $audit_school_service, AuditYearService $audit_year_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
    $this->auditSchoolService = $audit_school_service;
    $this->auditYearService = $audit_year_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('Drupal\ascend_audit\Services\AuditSchoolService'),
      $container->get('Drupal\ascend_audit\Services\AuditYearService')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Check if user is an auditor
    // if (!in_array('auditor', $this->currentUser->getRoles())) {
    //   return []; // Don't show block for non-auditors
    // }

    $username = $this->currentUser->getDisplayName();
    $school = $this->auditSchoolService->getWorkingSchoolName();
    $year = $this->auditYearService->getFormattedWorkingYear();

    return [
      '#theme' => 'auditor_working_info',
      '#name' => $username,
      '#school' => $school,
      '#year' => $year,
      '#cache' => [
        'contexts' => ['user'],
        'tags' => ['user:' . $this->currentUser->id()],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['user']; // does this invalidate on profile save as well?
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return ['user:' . $this->currentUser->id()]; // see above.
  }
}
