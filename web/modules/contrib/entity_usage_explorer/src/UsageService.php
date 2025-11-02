<?php

namespace Drupal\entity_usage_explorer;

use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItemBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldTypePluginManager;

/**
 * Provides a helper functions for module.
 */
class UsageService {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The field type manager.
   *
   * @var \Drupal\Core\Field\FieldTypePluginManager
   */
  protected $fieldTypeManager;

  /**
   * Constructs a new UsageService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Field\FieldTypePluginManager $field_type_manager
   *   The field type manager.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_type_manager, FieldTypePluginManager $field_type_manager) {
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->fieldTypeManager = $field_type_manager;
  }

  /**
   * Retrieves a list of content entity type IDs.
   *
   * @param bool $include_definition_data
   *   Whether to include additional definition data instead of just IDs.
   *
   * @return array
   *   An array of content entity type IDs.
   */
  public function loadContentEntityTypes($include_definition_data = FALSE) : array {
    $entity_definitions = $this->entityTypeManager->getDefinitions();

    $content_entity_types = [];
    $content_entity_definition_data = [];

    foreach ($entity_definitions as $entity_type_id => $definition) {
      if ($definition->entityClassImplements(ContentEntityInterface::class)) {
        if ($include_definition_data) {
          $content_entity_definition_data[$entity_type_id] = [
            'label_key' => $definition->getKey('label'),
            'canonical_url' => $definition->getLinkTemplate('canonical'),
          ];
        }
        else {
          $content_entity_types[] = $entity_type_id;
        }
      }
    }

    return $include_definition_data ? $content_entity_definition_data : $content_entity_types;
  }

  /**
   * Retrieves entity reference fields for a given entity type.
   *
   * @param string $entity_type
   *   The entity type to check.
   * @param string $bundle_to_query
   *   The target entity type to filter.
   *
   * @return array
   *   A list of matched fields with field name, table, column, and target type.
   */
  public function getEntityReferences($entity_type, $bundle_to_query) {
    $matches = [];
    $definitions = $this->fieldTypeManager->getDefinitions();

    $entity_reference_like_types = array_keys(array_filter($definitions, function ($definition) {
      $class = $definition['class'] ?? NULL;
      return $class && class_exists($class) && is_subclass_of($class, EntityReferenceItemBase::class);
    }));

    $query = $this->entityTypeManager->getStorage('field_storage_config')->getQuery()
      ->condition('entity_type', $entity_type)
      ->condition('type', $entity_reference_like_types, 'IN')
      ->accessCheck(FALSE);

    $field_ids = $query->execute();

    if (!empty($field_ids)) {
      $fields = $this->entityTypeManager->getStorage('field_storage_config')->loadMultiple($field_ids);

      foreach ($fields as $field) {
        $settings = $field->getSettings();
        $target_type = $settings['target_type'] ?? NULL;

        if ($target_type === $bundle_to_query) {
          $field_name = $field->getName();
          $matches[] = [
            'table' => "{$entity_type}__{$field_name}",
            'column' => "{$field_name}_target_id",
            'field_name' => $field_name,
            'target_type' => $target_type,
          ];
        }
      }
    }

    return $matches;
  }

  /**
   * Finds all references for given entity in menus and entity reference fields.
   *
   * @param string $target_entity
   *   The entity type being searched for references.
   * @param int $entity_id
   *   The ID of the target entity.
   *
   * @return array
   *   An associative array of entity reference records.
   */
  public function getEntityUsage(string $target_entity, int $entity_id): array {
    $data = [];
    $entity_types = $this->loadContentEntityTypes();
    foreach ($entity_types as $entity_type) {
      if ($entity_type == 'menu_link_content') {
        $menu_record = $this->getEntityUsageInMenu($target_entity, $entity_id);
        if (!empty($menu_record)) {
          $data[$entity_type] = $menu_record;
        }
        continue;
      }

      $matches = $this->getEntityReferences($entity_type, $target_entity);
      if (!empty($matches)) {
        $data[$entity_type] = [];
        foreach ($matches as $match) {
          $query = $this->database->select($match['table'], 't')
            ->fields('t')
            ->condition($match['column'], $entity_id)
            ->execute();

          $result = $query->fetchAll();
          if (!empty($result)) {
            $data[$entity_type] = array_merge($data[$entity_type], $result);
          }
        }
      }
    }
    if ($target_entity == 'paragraph') {
      $data['paragraphs_library_item'] = $this->getParagraphUsageInLibraryItems($entity_id, FALSE);
    }

    return $data;
  }

  /**
   * Counts the total number of references to a given entity.
   *
   * @param string $target_entity
   *   The entity type being searched for references.
   * @param int $entity_id
   *   The ID of the target entity.
   *
   * @return int
   *   The total count of found references.
   */
  public function getEntityUsageCount(string $target_entity, int $entity_id): int {
    $count = 0;
    $entity_types = $this->loadContentEntityTypes();
    foreach ($entity_types as $entity_type) {
      $matches = $this->getEntityReferences($entity_type, $target_entity);
      if (!empty($matches)) {
        foreach ($matches as $match) {
          $query = $this->database->select($match['table'], 't')
            ->condition($match['column'], $entity_id)
            ->countQuery()
            ->execute();
          $result = $query->fetchField();
          $count += !empty($result) ? $result : 0;
        }
      }
    }
    $count += !empty($this->getEntityUsageInMenu($target_entity, $entity_id, TRUE)) ? $this->getEntityUsageInMenu($target_entity, $entity_id, TRUE) : 0;
    if ($target_entity == 'paragraph') {
      $count += !empty($this->getParagraphUsageInLibraryItems($entity_id, TRUE)) ? $this->getParagraphUsageInLibraryItems($entity_id, TRUE) : 0;
    }

    return $count;
  }

  /**
   * Finds references to an entity in menu links.
   *
   * @param string $target_entity
   *   The entity type being searched for in menu links.
   * @param int $entity_id
   *   The ID of the target entity.
   * @param bool $only_count
   *   Whether to return match count (TRUE) or full records (FALSE).
   *
   * @return int|array|null
   *   The count of references if `$only_count` is TRUE, an array of matching
   *   records if FALSE, or NULL if no references are found.
   */
  public function getEntityUsageInMenu(string $target_entity, int $entity_id, $only_count = FALSE) {
    $query = $this->database->select('menu_link_content_data', 'm');
    $group = $query->orConditionGroup()
      ->condition('link__uri', "entity:{$target_entity}/{$entity_id}", 'LIKE')
      ->condition('link__uri', "internal:/{$target_entity}/{$entity_id}", 'LIKE');

    $result = $only_count ? $query->condition($group)->countQuery()->execute()->fetchField() : $query->fields('m')->condition($group)->execute()->fetchAll();

    return !empty($result) ? $result : NULL;
  }

  /**
   * Gets Paragraphs Library items using a given paragraph.
   *
   * @param int $paragraph_id
   *   The paragraph entity ID.
   * @param bool $only_count
   *   TRUE to return only the count, FALSE to return item records. Defaults to FALSE.
   *
   * @return int|array|null
   *   Count of items, array of records, or NULL if nothing found.
   */
  public function getParagraphUsageInLibraryItems(int $paragraph_id, bool $only_count = FALSE) {
    $query = $this->database->select('paragraphs_library_item_field_data', 'plifd')
      ->condition('plifd.paragraphs__target_id', $paragraph_id, '=');

    return $only_count ? (int) $query->countQuery()->execute()->fetchField() : $query->fields('plifd', ['id', 'langcode', 'paragraphs__target_id', 'label', 'changed'])
      ->execute()
      ->fetchAll();
  }

}
