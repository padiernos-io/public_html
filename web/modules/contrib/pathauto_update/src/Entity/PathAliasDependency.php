<?php

namespace Drupal\pathauto_update\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyInterface;

/**
 * Defines the path alias dependency entity.
 *
 * @ContentEntityType(
 *   id = "path_alias_dependency",
 *   label = @Translation("Path alias dependency"),
 *   base_table = "path_alias_dependency",
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" : "did",
 *   },
 * )
 */
class PathAliasDependency extends ContentEntityBase implements PathAliasDependencyInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['did'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity alias dependency ID'))
      ->setDescription(t('The entity alias dependency ID.'))
      ->setReadOnly(TRUE);

    $fields['path_alias_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Path alias ID'))
      ->setDescription(t('The ID of the path alias.'));

    $fields['dependency_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Dependency type'))
      ->setDescription(t('The dependency type.'));

    $fields['dependency_value'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Dependency value'))
      ->setDescription(t('The dependency value.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The date when the entity alias dependency was created.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getPathAlias(): ?PathAliasInterface {
    return $this->entityTypeManager()
      ->getStorage('path_alias')
      ->load($this->get('path_alias_id')->value);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencyType(): string {
    return $this->get('dependency_type')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencyValue(): string {
    return $this->get('dependency_value')->value;
  }

}
