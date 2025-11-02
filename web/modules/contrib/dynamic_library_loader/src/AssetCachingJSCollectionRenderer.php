<?php

namespace Drupal\dynamic_library_loader;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\JsCollectionRenderer;
use Drupal\Core\Asset\AssetQueryStringInterface;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders JS assets.
 */
class AssetCachingJSCollectionRenderer extends JsCollectionRenderer implements AssetCollectionRendererInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected StateInterface $state;

  /**
   * Constructs a new AssetCachingJSCollectionRenderer.
   *
   * @param \Drupal\Core\Asset\AssetQueryStringInterface $asset_query_string
   *   The asset query string service.
   * @param \Drupal\Core\File\FileUrlGenerator $file_url_generator
   *   The file URL generator service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(
    AssetQueryStringInterface $asset_query_string,
    FileUrlGenerator $file_url_generator,
    TimeInterface $time,
    StateInterface $state
  ) {
    parent::__construct($asset_query_string, $file_url_generator, $time);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function render(array $js_assets) {
    $elements = parent::render($js_assets);

    // Retrieve the cache-busting query string from the state system.
    $query_string = $this->state->get('system.css_js_query_string', '0');

    foreach ($elements as $index => $el) {
      if (!empty($el['#attributes']['src']) && !UrlHelper::isExternal($el['#attributes']['src'])) {
        $query_string_separator = (strpos($el['#attributes']['src'], '?') !== FALSE) ? '&' : '?';
        $elements[$index]['#attributes']['src'] .= $query_string_separator . $query_string;
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('asset.query_string'),
      $container->get('file_url_generator'),
      $container->get('datetime.time'),
      $container->get('state')
    );
  }

}
