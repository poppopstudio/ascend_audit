<?php

namespace Drupal\ascend_audit\Entity;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;

/**
 * Provides the Audit entity.
 *
 * @ContentEntityType(
 *   id = "audit",
 *   label = @Translation("Audit"),
 *   label_collection = @Translation("Audits"),
 *   label_singular = @Translation("audit"),
 *   label_plural = @Translation("audits"),
 *   label_count = @PluralTranslation(
 *     singular = "@count audit",
 *     plural = "@count audits",
 *   ),
 *   base_table = "audit",
 *   revision_table = "audit_revision",
 *   show_revision_ui = TRUE,
 *   collection_permission = "access audit overview",
 *   handlers = {
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\entity_admin_handlers\SingleBundleEntity\SingleBundleEntityHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *     "form" = {
 *       "default" = "Drupal\ascend_audit\Form\AuditForm",
 *       "edit" = "Drupal\ascend_audit\Form\AuditForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "list_builder" = "Drupal\ascend_audit\Entity\Handler\AuditListBuilder",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *   },
 *   admin_permission = "administer audit entities",
 *   entity_keys = {
 *     "id" = "audit_id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "revision" = "revision_id",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   field_ui_base_route = "entity.audit.field_ui_base",
 *   links = {
 *     "add-form" = "/audit/add",
 *     "canonical" = "/audit/{audit}",
 *     "collection" = "/admin/content/audit",
 *     "delete-form" = "/audit/{audit}/delete",
 *     "edit-form" = "/audit/{audit}/edit",
 *     "field-ui-base" = "/admin/structure/audit",
 *     "version-history" = "/admin/structure/audit/{audit}/revisions",
 *     "revision" = "/admin/structure/audit/{audit}/revisions/{audit_revision}/view",
 *     "revision-revert-form" = "/admin/structure/audit/{audit}/revisions/{audit_revision}/revert",
 *     "revision-delete-form" = "/admin/structure/audit/{audit}/revisions/{audit_revision}/delete",
 *   },
 * )
 */
class Audit extends EditorialContentEntityBase implements AuditInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    // Review this field
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Title"))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -5])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid']
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['status']
      ->setLabel(t("Published"))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 120,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Authored on"))
      ->setDescription(t("The date & time that the audit was created."))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t("Changed"))
      ->setDescription(t("The time that the audit was last edited."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t("Category"))
      ->setDescription(t("The audit item's category."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default:taxonomy_term')
      ->setSetting("handler_settings", [
        'target_bundles' => [
          'category' => 'category',
        ],
        'sort' => [
          'field' => 'name',
          'direction' => 'asc',
        ],
        'auto_create' => FALSE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'cshs',
        'weight' => -10,
        'settings' => [
          // 'force_deepest' => TRUE, // nope
          'parent' => 0, // set this to the top level of focus areas.
          'none_label' => ' - Select category - ',
        ]
      ])
      ->setDisplayOptions('view', [
        'type' => 'cshs_full_hierarchy',
        'label' => 'inline',
        'weight' => -10,
        'settings' => [
          // 'format' => '[term:parents:join: » ] » [term:description]',
          'link' => FALSE,
          'clear' => TRUE,
        ]
      ]);

    $fields['school'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t("School"))
      ->setDescription(t("The audit item's school."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'school')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ),
    ));

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t("Year"))
      ->setDescription(t("The audit item's year."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
