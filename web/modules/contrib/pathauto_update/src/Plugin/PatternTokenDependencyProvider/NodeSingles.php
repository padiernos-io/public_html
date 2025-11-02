<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\node\NodeInterface;
use Drupal\node_singles\Service\NodeSinglesInterface;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for [node_singles] tokens.
 *
 * @PatternTokenDependencyProvider(
 *   type = "node_singles",
 *   provider = "node_singles",
 * )
 */
class NodeSingles extends PatternTokenDependencyProviderBase {

  /**
   * The node singles service.
   *
   * @var \Drupal\node_singles\Service\NodeSinglesInterface
   */
  protected NodeSinglesInterface $nodeSingles;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    if ($container->has('node_singles')) {
      $instance->nodeSingles = $container->get('node_singles');
    }

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    if (!isset($this->nodeSingles)) {
      return;
    }

    foreach ($tokens as $name => $original) {
      [$singleName, $subType] = explode(':', $name);

      $single = $this->nodeSingles->getSingleByBundle($singleName);
      if (!$single instanceof NodeInterface) {
        continue;
      }

      if ($subType === 'url') {
        if ($alias = $this->getPathAliasByEntity($single)) {
          $dependencies->addEntity($alias);
        }

        continue;
      }

      if ($entityTokens = $this->tokens->findWithPrefix($tokens, $singleName)) {
        $this->addDependenciesByType('node', $entityTokens, ['node' => $single], $options, $dependencies);
        $dependencies->addEntity($single);
      }
    }
  }

}
