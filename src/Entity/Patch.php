<?php

namespace Drupal\patch_revision\Entity;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\patch_revision\DiffService;
use Drupal\patch_revision\Plugin\FieldPatchPluginInterface;
use Drupal\user\Entity\User;

/**
 * Defines the Patch entity.
 *
 * @ingroup patch_revision
 *
 * @ContentEntityType(
 *   id = "patch",
 *   label = @Translation("Patch"),
 *   handlers = {
 *     "view_builder" = "Drupal\patch_revision\PatchViewBuilder",
 *     "list_builder" = "Drupal\patch_revision\PatchListBuilder",
 *     "views_data" = "Drupal\patch_revision\Entity\PatchViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\patch_revision\Form\PatchForm",
 *       "apply" = "Drupal\patch_revision\Form\PatchApplyForm",
 *       "edit" = "Drupal\patch_revision\Form\PatchForm",
 *       "delete" = "Drupal\patch_revision\Form\PatchDeleteForm",
 *     },
 *     "access" = "Drupal\patch_revision\PatchAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\patch_revision\PatchHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "patch",
 *   admin_permission = "administer patch entities",
 *   list_cache_contexts = {
 *     "user.permissions",
 *     "languages",
 *     "timezone",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "created" = "created",
 *     "changed" = "changed",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "rtype" = "rtype",
 *     "rbundle" = "rbundle",
 *     "rid" = "rid",
 *     "rvid" = "rvid",
 *     "uid" = "uid",
 *     "patch" = "patch",
 *     "message" = "message",
 *   },
 *   links = {
 *     "canonical" = "/patch/{patch}",
 *     "apply-form" = "/patch/{patch}/apply",
 *     "edit-form" = "/patch/{patch}/edit",
 *     "delete-form" = "/patch/{patch}/delete",
 *     "collection" = "/patch",
 *   }
 * )
 */
class Patch extends ContentEntityBase {

  /**
   * @var EntityInterface
   */
  private $originalEntity;

  /**
   * @var DiffService
   */
  private $diffService;

  /**
   * @var User
   */
  private $creator;

  /**
   * @var EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * @var array
   */
  private $entityFieldMap;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
  }

  /**
   * Returns lazy Entity field manager.
   * @return EntityFieldManager
   */
  protected function getEntityFieldManager() {
    if (!$this->entityFieldManager) {
      $this->entityFieldManager = \Drupal::service('entity_field.manager');
    }
    return $this->entityFieldManager;
  }

  /**
   * Returns lazy Entity field map.
   *
   * @return array
   *   The complete field map.
   */
  protected function getEntityFieldMap() {
    if (!$this->entityFieldMap) {
      $this->entityFieldMap = $this->getEntityFieldManager()->getFieldMap();
    }
    return $this->entityFieldMap;
  }


  /**
   * Field type property.
   *
   * @return string
   */
  protected function getEntityFieldType($entity_type, $field_name) {
    $map = $this->getEntityFieldMap();
    return (isset($map[$entity_type][$field_name]))
      ? $map[$entity_type][$field_name]['type']
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields[$entity_type->getKey('id')] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('ID'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields[$entity_type->getKey('created')] = BaseFieldDefinition::create('created')
      ->setLabel(t('Added on'))
      ->setDescription(t('The time that the patch was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the node was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields[$entity_type->getKey('uuid')] = BaseFieldDefinition::create('uuid')
      ->setLabel(new TranslatableMarkup('UUID'))
      ->setReadOnly(TRUE);

    $fields[$entity_type->getKey('status')] = BaseFieldDefinition::create('integer')
      ->setLabel(t('status'))
      ->setDescription(t('The status of the patch.'))
      ->setSetting('size', 'small');

    $fields[$entity_type->getKey('rtype')] = BaseFieldDefinition::create('string')
      ->setLabel(t('type of referred node.'))
      ->setRevisionable(FALSE)
      ->setDefaultValue('');

    $fields[$entity_type->getKey('rbundle')] = BaseFieldDefinition::create('string')
      ->setLabel(t('bundle of referred node'))
      ->setRevisionable(FALSE)
      ->setDefaultValue('');

    $fields[$entity_type->getKey('rid')] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Referred node'))
      ->setDescription(t('The referred node of the patch.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'node');

    $fields[$entity_type->getKey('rvid')] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Node version id'))
      ->setReadOnly(TRUE)
      ->setDefaultValue('')
      ->setSetting('unsigned', TRUE);

    $fields[$entity_type->getKey('uid')] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Creator'))
      ->setDescription(t('The creator of the patch.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'user');

    $fields[$entity_type->getKey('patch')] = BaseFieldDefinition::create('map')
      ->setLabel(new TranslatableMarkup('Patch'))
      ->setReadOnly(TRUE)
      ->setRevisionable(FALSE)
      ->setDefaultValue('');

    $fields[$entity_type->getKey('message')] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Revision log message'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 25,
        'settings' => [
          'rows' => 3,
        ],
      ]);

    return $fields;
  }

  /**
   * Returns the referred entity.
   *
   * @return \Drupal\node\NodeInterface|FALSE
   */
  public function originalEntity() {
    if (!isset($this->originalEntity)) {
      /** @var \Drupal\node\NodeInterface[] $orig_entity */
      $orig_entity = $this->get('rid')->referencedEntities();
      $this->originalEntity = count($orig_entity) ? $orig_entity[0] : FALSE;
    }
    return $this->originalEntity;
  }

  /**
   * Returns the Creator user.
   *
   * @return \Drupal\user\Entity\User|FALSE
   */
  public function getCreator() {
    if (!isset($this->creator)) {
      /** @var \Drupal\user\Entity\User[] $creator */
      $creator = $this->get('uid')->referencedEntities();
      $this->creator = count($creator) ? $creator[0] : FALSE;
    }
    return $this->creator;
  }

  /**
   * Returns the Diff entity.
   *
   * @return DiffService
   */
  public function getDiffService() {
    if (!isset($this->diffService)) {
      $this->diffService = \Drupal::service('patch_revision.diff');
    }
    return $this->diffService;
  }

  /**
   * Returns the plugin belongs to the field type.
   *
   * @param $field_name
   *   The field name mashine readable.
   *
   * @return FieldPatchPluginInterface|FALSE
   */
  public function getPatchPluginFromOrigFieldName($entity_id, $field_name) {
    if ($field_type = $this->getEntityFieldType($entity_id, $field_name)) {
      return $this->getDiffService()->getPluginFromFieldType($field_type);
    }
    else return FALSE;
  }


  /**
   * Returns the label belongs to the field type.
   *
   * @return TranslatableMarkup|string
   */
  public function getOrigFieldLabel($field_name) {
    if (FALSE && $orig_entity = $this->originalEntity()) {
      return $orig_entity->getFieldDefinition($field_name)->getLabel();
    }
    else {
      return ucfirst(str_replace('_', ' ', $field_name));
    }

  }


}
