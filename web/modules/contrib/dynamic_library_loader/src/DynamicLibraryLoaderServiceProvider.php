<?php

namespace Drupal\dynamic_library_loader;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies core services to include asset cache busting.
 */
class DynamicLibraryLoaderServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override CSS collection renderer service.
    if ($container->hasDefinition('asset.css.collection_renderer')) {
      $definition = $container->getDefinition('asset.css.collection_renderer');
      $definition->setClass('Drupal\\dynamic_library_loader\\AssetCachingCSSCollectionRenderer');
      $definition->addArgument(new Reference('state'));
    }

    // Override JS collection renderer service.
    if ($container->hasDefinition('asset.js.collection_renderer')) {
      $definition = $container->getDefinition('asset.js.collection_renderer');
      $definition->setClass('Drupal\\dynamic_library_loader\\AssetCachingJSCollectionRenderer');
      $definition->addArgument(new Reference('state'));
    }
  }

}