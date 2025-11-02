<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\Entity\Storage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\paragraph_block\ParagraphBlockServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom storage handler for block content entities.
 */
class CustomBlockContentStorage extends SqlContentEntityStorage {

  /**
   * Constructs a CustomBlockContentStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache backend to be used.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\paragraph_block\ParagraphBlockServiceInterface $paragraphBlockService
   *   The paragraph block service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    Connection $database,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    MemoryCacheInterface $memory_cache,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityTypeManagerInterface $entity_type_manager,
    protected readonly ParagraphBlockServiceInterface $paragraphBlockService
  ) {
    parent::__construct($entity_type, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('paragraph_block.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doCreate(array $values) {
    // If it's one of our paragraph block types, change the type so drupal will
    // just work if we would be on a paragraph_block block type.
    $paragraph_type = NULL;
    if (in_array($values['type'], $this->paragraphBlockService->getParagraphBlockTypeKeys(), TRUE)) {
      $paragraph_type = $values['type'];
      $values['type'] = ParagraphBlockServiceInterface::BLOCK_TYPE;
    }

    // Create the block content entity.
    $entity = parent::doCreate($values);

    // Add an empty paragraph of the selected type to enhance the UX.
    if ($paragraph_type && $entity->bundle() === ParagraphBlockServiceInterface::BLOCK_TYPE) {
      $field_name = ParagraphBlockServiceInterface::FIELD_NAME;
      if ($entity->{$field_name}->isEmpty()) {
        $paragraph = $this->entityTypeManager->getStorage('paragraph')->create([
          'type' => $paragraph_type,
        ]);
        $entity->{$field_name}[] = $paragraph;
      }
    }

    return $entity;
  }

}
