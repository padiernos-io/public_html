<?php

namespace Drupal\backstop_generator\Entity;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the storage handler for BackstopProfile entities.
 */
class BackstopProfileStorage extends ConfigEntityStorage implements EntityStorageInterface {

  /**
   * Constructs a new BackstopProfileStorage object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The UUID service.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    UuidInterface $uuid,
    MemoryCacheInterface $memory_cache,
    EntityTypeInterface $entity_type,
    LanguageManagerInterface $language_manager,
  ) {
    parent::__construct($entity_type, $config_factory, $uuid, $language_manager, $memory_cache);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    // Create a new instance of BackstopProfile with the provided values.
    return new static(
      $container->get('config.factory'),
      $container->get('uuid'),
      $container->get('entity.memory_cache'),
      $entity_type,
      $container->get('language_manager'),
      $container->get('file_system'),
      $container->get('logger.channel.backstop_generator')
    );
  }

}
