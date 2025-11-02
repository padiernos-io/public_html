<?php

namespace Drupal\pathauto_update;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto\AliasStorageHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for pattern token dependency providers.
 */
abstract class PatternTokenDependencyProviderBase extends PluginBase implements PatternTokenDependencyProviderInterface, ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected Token $tokens;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The alias storage helper.
   *
   * @var \Drupal\pathauto\AliasStorageHelperInterface
   */
  protected AliasStorageHelperInterface $aliases;

  /**
   * The pattern token dependency provider manager.
   *
   * @var \Drupal\pathauto_update\PatternTokenDependencyProviderManager
   */
  protected PatternTokenDependencyProviderManager $manager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->tokens = $container->get('token');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->aliases = $container->get('pathauto.alias_storage_helper');
    $instance->manager = $container->get('plugin.manager.pattern_token_dependency_provider');

    return $instance;
  }

  /**
   * Add dependencies for the given tokens.
   */
  protected function addDependenciesByType(string $type, array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    $this->manager->createInstance($type)
      ->addDependencies($tokens, $data, $options, $dependencies);
  }

  /**
   * Get the path alias entity of a given entity.
   */
  protected function getPathAliasByEntity(EntityInterface $entity): ?PathAliasInterface {
    try {
      return $this->getPathAlias(
        '/' . $entity->toUrl()->getInternalPath(),
      );
    }
    catch (EntityMalformedException $e) {
      return NULL;
    }
  }

  /**
   * Get the path alias entity of a given path.
   */
  protected function getPathAlias(string $path): ?PathAliasInterface {
    $entities = $this->entityTypeManager
      ->getStorage('path_alias')
      ->loadByProperties([
        'path' => '/' . ltrim($path, '/'),
      ]);

    return array_pop($entities);
  }

}
