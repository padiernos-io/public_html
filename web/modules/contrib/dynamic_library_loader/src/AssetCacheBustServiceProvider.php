<?php

namespace Drupal\dynamic_library_loader;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class AssetCacheBustServiceProvider.
 *
 * Class to override Drupal core CSSCollectionRenderer
 * and JsCollectionRenderer services.
 */
class AssetCacheBustServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Override CSS collection renderer service.
    $current_css_service_def = $container->getDefinition('asset.css.collection_renderer');
    $current_css_service_def->setClass("Drupal\asset_cache_bust\AssetCachingCSSCollectionRenderer");
    $current_css_service_def->addArgument(new Reference("state"));

    // Override JS collection renderer service.
    $current_js_service_def = $container->getDefinition('asset.js.collection_renderer');
    $current_js_service_def->setClass("Drupal\asset_cache_bust\AssetCachingJSCollectionRenderer");
    $current_js_service_def->addArgument(new Reference("state"));
  }

}
