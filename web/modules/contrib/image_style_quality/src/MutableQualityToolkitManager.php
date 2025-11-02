<?php

namespace Drupal\image_style_quality;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Manage setting quality values on different toolkits.
 */
class MutableQualityToolkitManager extends DefaultPluginManager implements MutableQualityToolkitManagerInterface {

  /**
   * The active quality toolkit.
   *
   * @var array|null
   */
  protected ?array $activeToolkit = NULL;

  public function __construct(
    ModuleHandlerInterface $module_handler,
    CacheBackendInterface $cache_backend,
    protected ConfigFactoryInterface $configFactory,
  ) {
    $this->moduleHandler = $module_handler;
    $this->alterInfo('mutable_quality_toolkits');
    $this->setCacheBackend($cache_backend, 'mutable_quality_toolkits');
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveToolkit(): array {
    if ($this->activeToolkit === NULL) {
      $this->activeToolkit = $this->getDefinition($this->configFactory->get('system.image')->get('toolkit'));
    }
    return $this->activeToolkit;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery(): YamlDiscovery {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('mutable_quality_toolkits', $this->moduleHandler->getModuleDirectories());
    }
    return $this->discovery;
  }

}
