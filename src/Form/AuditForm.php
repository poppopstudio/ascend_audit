<?php

namespace Drupal\ascend_audit\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the default form handler for the Audit entity.
 */
class AuditForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    /** @var \Drupal\ascend_audit\Entity\Audit $audit */
    $audit = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit @type</em> @title', [
        '@type' => 'audit',
        '@title' => $audit->label(),
      ]);
    }

    $form['advanced']['#attributes']['class'][] = 'entity-meta'; // ?

    $form['meta'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -100,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
      // '#access' => $this->currentUser()->hasPermission('update any audit'),
    ];
    $form['meta']['published'] = [
      '#type' => 'item',
      '#markup' => $audit->isPublished() ? $this->t('Published') : $this->t('Not published'),
      '#access' => !$audit->isNew(),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']],
    ];
    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      // '#markup' => !$audit->isNew() ? $this->dateFormatter->format($audit->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['entity-meta__last-saved']],
    ];
    $form['meta']['author'] = [
      '#type' => 'item',
      '#title' => $this->t('Author'),
      '#markup' => $audit->getOwner()->getAccountName(),
      '#wrapper_attributes' => ['class' => ['entity-meta__author']],
    ];

    // Get the category from the audit entity.
    $details_category = $audit->get('category')->target_id;

    $form['audit_standards'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -20,
      '#title' => $this->t("Teachers' Standards"),
      '#open' => TRUE,
      // '#access' => $audit->currentUser->hasRoles('auditor'),
    ];
    $form['audit_standards']['details'] = [
      '#type' => 'container',
      'view' => views_embed_view('audit_standards', 'embed_1', $details_category),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']], // Stolen but just works.
    ];

    // Check the resource kit is installed - does this need DI?
    if (\Drupal::service('module_handler')->moduleExists('ascend_resource')) {
      $form['audit_resources'] = [
        '#type' => 'details',
        '#group' => 'advanced',
        '#weight' => -10,
        '#title' => $this->t("Category resources"),
        '#open' => TRUE,
      ];
      $form['audit_resources']['details'] = [
        '#type' => 'container',
        'view' => views_embed_view('category_resources', 'embed_2', $details_category),
        '#wrapper_attributes' => ['class' => ['entity-meta__title']],
      ];
    }

    $form['audit_info'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -15,
      '#title' => t('Audit info'),
      '#open' => TRUE,
      // '#access' => $this->currentUser->hasPermission('administer nodes'),
    ];

    $form['audit_info']['details'] = [
      '#type' => 'container',
      'view' => views_embed_view('ascend_audit_info', 'embed_1', $details_category),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved = parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->entity->toUrl('canonical'));

    return $saved;
  }

}
