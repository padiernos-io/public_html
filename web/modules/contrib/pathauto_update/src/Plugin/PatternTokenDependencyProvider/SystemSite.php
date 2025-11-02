<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for [site] tokens.
 *
 * @PatternTokenDependencyProvider(
 *   type = "site",
 * )
 */
class SystemSite extends PatternTokenDependencyProviderBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    foreach ($tokens as $token => $rawToken) {
      switch ($token) {
        case 'name':
        case 'slogan':
        case 'mail':
          $dependencies->addConfig($this->configFactory->get('system.site'));
      }
    }
  }

}
