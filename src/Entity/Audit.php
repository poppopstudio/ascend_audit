<?php

namespace Drupal\ascend_audit\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
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
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\ascend_audit\Form\AuditForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "list_builder" = "Drupal\ascend_audit\Entity\Handler\AuditListBuilder",
 *   },
 *   admin_permission = "administer audit entities",
 *   entity_keys = {
 *     "id" = "audit_id",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "revision" = "revision_id",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   field_ui_base_route = "entity.audit.admin_form",
 *   links = {
 *     "add-form" = "/audit/add",
 *     "canonical" = "/audit/{audit}",
 *     "collection" = "/admin/content/audit",
 *     "delete-form" = "/audit/{audit}/delete",
 *     "edit-form" = "/audit/{audit}/edit",
 *   },
 * )
 */
class Audit extends ContentEntityBase implements AuditInterface {

  use EntityChangedTrait;

  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t("Title"))
      ->setRequired(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', ['type' => 'string_textfield', 'weight' => -5])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t("Created"))
      ->setDescription(t("The time that the entity was created."));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t("Changed"))
      ->setDescription(t("The time that the entity was last edited."))
      ->setRevisionable(TRUE);

    $fields['category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t("Category"))
      ->setDescription(t("TODO: description of field."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['school'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t("School"))
      ->setDescription(t("TODO: description of field."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t("Year"))
      ->setDescription(t("TODO: description of field."))
      ->setRevisionable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
