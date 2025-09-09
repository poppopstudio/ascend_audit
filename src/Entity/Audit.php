<?php

namespace Drupal\ascend_audit\Entity;

use Drupal\ascend_audit\Services\AuditSchoolService;
use Drupal\ascend_audit\Services\AuditYearService;
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
 *   label = @Translation("Audit item"),
 *   label_collection = @Translation("Audit items"),
 *   label_singular = @Translation("audit item"),
 *   label_plural = @Translation("audit items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count audit item",
 *     plural = "@count audit items",
 *   ),
 *   base_table = "audit",
 *   revision_table = "audit_revision",
 *   show_revision_ui = TRUE,
 *   collection_permission = "access audit overview",
 *   handlers = {
 *     "access" = "Drupal\ascend_audit\Entity\Handler\AuditAccess",
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
 *     "views_data" = "Drupal\ascend_audit\Entity\Handler\AuditViewsData",
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
 *   constraints = {
 *     "UniqueAuditConstraint" = {}
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
        'label' => 'inline',
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
      ->setDescription(t("The factor for this audit item."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->addConstraint('UniqueAudit') // Prevent duplicate entries.
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
          'parent' => 0, // Gets set to the top level of focus areas.
          'hierarchy_depth' => 2,
          'required_depth' => 2,
          'save_lineage' => FALSE,
          'force_deepest' => FALSE,
          'none_label' => ' - Select focus area - ',
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
      ->setDefaultValueCallback('Drupal\ascend_audit\Entity\Audit::getDefaultSchool')
      ->setSetting('target_type', 'school')
      ->setSetting('handler', 'default:school')
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
      ->setDescription(t('Working school year in YY format (e.g. 24 for 2024/25).'))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE)
      ->setCardinality(1)
      ->setDefaultValueCallback('Drupal\ascend_audit\Entity\Audit::getDefaultYear')
      ->setSettings([
        'max_length' => 2,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', array(
        'label' => 'inline',
        'type' => 'label',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'number',
        'weight' => 0,
      ));

    return $fields;
  }

  /**
   * Use a computed label instead of storing titles.
   */
  public function label() {
    $category_id = $this->get('category')->target_id ?? 'X';
    $school_id = $this->get('school')->target_id ?? 'X';
    $year = $this->get('year')->value ?? 'X';

    return "s{$school_id}.c{$category_id}.y{$year}";
  }

  /**
   * Default value callback for year field.
   */
  public static function getDefaultYear() {
    return \Drupal::service(AuditYearService::class)->getWorkingYear();
  }

  /**
   * Default value callback for school field.
   */
  public static function getDefaultSchool() {
    return \Drupal::service(AuditSchoolService::class)->getWorkingSchool();
  }
}
