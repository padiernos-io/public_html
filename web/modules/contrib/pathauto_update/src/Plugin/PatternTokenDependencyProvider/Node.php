<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for [node] tokens.
 *
 * @PatternTokenDependencyProvider(
 *   type = "node",
 * )
 *
 * @see hook_tokens
 * @see _menu_ui_tokens
 */
class Node extends PatternTokenDependencyProviderBase {

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected MenuLinkManagerInterface $menuLinkManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->menuLinkManager = $container->get('plugin.manager.menu.link');
    $instance->moduleHandler = $container->get('module_handler');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    $node = $data['node'];
    $link = $this->getMenuLinkByNode($node);

    foreach ($tokens as $token => $rawToken) {
      if ($token === 'author') {
        $storage = $this->entityTypeManager
          ->getStorage('user');

        $dependencies->addEntity($node->getOwner() ?? $storage->load(0));
      }

      if ($token === 'created') {
        $storage = $this->entityTypeManager
          ->getStorage('date_format');

        $dependencies->addEntity($storage->load('medium'));
      }

      if ($link && $token === 'menu-link') {
        $this->addDependenciesByType('menu-link', ['menu-link:title'], ['menu-link' => $link], $options, $dependencies);
      }
    }

    if ($createdTokens = $this->tokens->findWithPrefix($tokens, 'created')) {
      $this->addDependenciesByType('date', $createdTokens, ['date' => $node->getCreatedTime()], $options, $dependencies);
    }

    if ($link && $menuTokens = $this->tokens->findWithPrefix($tokens, 'menu-link')) {
      $this->addDependenciesByType('menu-link', $menuTokens, ['menu-link' => $link], $options, $dependencies);
    }

    $tokenData = [
      'entity_type' => 'node',
      'entity' => $node,
      'token_type' => 'node',
    ];

    $this->addDependenciesByType('entity', $tokens, $tokenData, $options, $dependencies);
  }

  /**
   * Get menu link by node.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface|\Drupal\menu_link_content\MenuLinkContentInterface|null
   *   The menu link.
   */
  protected function getMenuLinkByNode(NodeInterface $node) {
    if ($node->getFieldDefinition('menu_link') && $menuLink = $node->menu_link->entity) {
      return $menuLink;
    }

    $url = $node->toUrl();
    $links = $this->menuLinkManager->loadLinksByRoute($url->getRouteName(), $url->getRouteParameters());

    if (empty($links)) {
      return NULL;
    }

    $this->moduleHandler->loadInclude('token', 'inc', 'token.tokens');

    return _token_menu_link_best_match($node, $links);
  }

}
