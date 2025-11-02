<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\entity_display_processor\Attribute\EntityDisplayProcessor;
use Drupal\entity_display_processor\Plugin\EntityDisplayProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Plugin manager for entity display processors.
 */
class EntityDisplayProcessorManager extends DefaultPluginManager {

  /**
   * Constructor.
   *
   * @param \Traversable $namespaces
   *   Searchable PSR-4 namespace directories.
   *   Format: $[$namespace][] = $directory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    #[Autowire(service: 'container.namespaces')]
    \Traversable $namespaces,
    #[Autowire(service: 'cache.discovery')]
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      'Plugin/EntityDisplayProcessor',
      $namespaces,
      $module_handler,
      EntityDisplayProcessorInterface::class,
      EntityDisplayProcessor::class,
    );
    $this->alterInfo('entity_display_processor');
    $this->setCacheBackend($cache_backend, 'entity_display_processor_info');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []): EntityDisplayProcessorInterface {
    if (!$plugin_id) {
      throw new PluginException("Plugin id is empty.");
    }
    if (!is_string($plugin_id)) {
      throw new PluginException("Plugin id is not a string.");
    }
    $instance = parent::createInstance($plugin_id, $configuration);
    if (!$instance) {
      throw new PluginNotFoundException("Plugin '$plugin_id' not found.");
    }
    if (!$instance instanceof EntityDisplayProcessorInterface) {
      throw new InvalidPluginDefinitionException(sprintf(
        "Expected a %s object, found %s, for plugin id '%s'.",
        EntityDisplayProcessorInterface::class,
        get_debug_type($instance),
        $plugin_id,
      ));
    }
    return $instance;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   The instance cannot be obtained.
   */
  public function getInstance(array $options): EntityDisplayProcessorInterface {
    return $this->createInstance(
      $options['id'] ?? NULL,
      $options['settings'] ?? [],
    );
  }

}
