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
    $school = $this->auditSchoolService->getWorkingSchoolName();
    $year = $this->auditYearService->getFormattedWorkingYear();

    return [
      '#theme' => 'auditor_working_info',
      '#school' => $school,
      '#year' => $year,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return [
      'ascend_active_school',
      'ascend_current_year',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    /**
     * Use the school's cache tags, as we need to invalidate this block if the
     * school changes its name for instance.
     */
    $school = $this->auditSchoolService->getWorkingSchoolEntity();
    return $school ? $school->getCacheTags() : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->auditYearService->getWorkingYearCacheExpiry();
  }

}
