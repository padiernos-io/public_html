<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\Entity\Storage;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\paragraph_block\ParagraphBlockServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom storage handler for block content type config entities.
 */
class CustomBlockContentTypeStorage extends ConfigEntityStorage {

  /**
   * Constructs a ConfigEntityStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache backend.
   * @param \Drupal\paragraph_block\ParagraphBlockServiceInterface $paragraphBlockService
   *   The paragraph block service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    ConfigFactoryInterface $config_factory,
    UuidInterface $uuid_service,
    LanguageManagerInterface $language_manager,
    MemoryCacheInterface $memory_cache,
    protected readonly ParagraphBlockServiceInterface $paragraphBlockService
  ) {
    parent::__construct($entity_type, $config_factory, $uuid_service, $language_manager, $memory_cache);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('paragraph_block.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    // Load the existing bundles.
    $bundles = parent::loadMultiple($ids);

    // Hide from config listings.
    if (!$this->overrideFree || (isset($ids[0]) && in_array($ids[0], $this->paragraphBlockService->getParagraphBlockTypeKeys(), TRUE))) {
      // Add fake bundles dynamically.
      $fake_bundles = $this->getFakeBundles();
      foreach ($fake_bundles as $id => $definition) {
        if (!isset($bundles[$id])) {
          // Dynamically create a new bundle entity.
          $bundles[$id] = $this->create($definition);
        }
      }
    }

    return $bundles;
  }

  /**
   * Returns a list of fake bundles.
   *
   * @return array
   *   An array of fake bundle definitions keyed by their IDs.
   */
  protected function getFakeBundles(): array {
    $fake_bundles = [];
    foreach ($this->paragraphBlockService->getParagraphBlockTypes() as $key => $definition) {
      $fake_bundles[$key] = [
        'id' => $key,
        'label' => $definition->label(),
        'description' => 'This is a dynamically generated bundle.',
        'status' => TRUE,
      ];
    }

    return $fake_bundles;
  }

}
