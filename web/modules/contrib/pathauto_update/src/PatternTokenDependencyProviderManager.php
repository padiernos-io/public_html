<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\pathauto_update\Annotation\PatternTokenDependencyProvider;

/**
 * Manages PatternTokenDependencyProvider plugins.
 *
 * @method PatternTokenDependencyProviderInterface createInstance($plugin_id, array $configuration = [])
 */
class PatternTokenDependencyProviderManager extends DefaultPluginManager {

  /**
   * Constructs a new PatternTokenDependencyProviderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cacheBackend,
    ModuleHandlerInterface $moduleHandler
  ) {
    parent::__construct(
      'Plugin/PatternTokenDependencyProvider',
      $namespaces,
      $moduleHandler,
      PatternTokenDependencyProviderInterface::class,
      PatternTokenDependencyProvider::class
    );
    $this->alterInfo('pathauto_update_pattern_token_dependency_provider_info');
    $this->setCacheBackend($cacheBackend, 'pathauto_update_pattern_token_dependency_providers');
  }

}
