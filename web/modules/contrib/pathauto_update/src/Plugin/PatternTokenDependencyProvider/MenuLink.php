<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Drupal\url_entity\UrlEntityExtractorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for tokens with menu links.
 *
 * @PatternTokenDependencyProvider(
 *   type = "menu-link",
 * )
 */
class MenuLink extends PatternTokenDependencyProviderBase {

  use MenuLinkEntityTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected MenuLinkManagerInterface $menuLinkManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected RouteProviderInterface $routeProvider;

  /**
   * The URL entity extractor.
   *
   * @var \Drupal\url_entity\UrlEntityExtractorInterface
   */
  protected UrlEntityExtractorInterface $urlEntityExtractor;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->languageManager = $container->get('language_manager');
    $instance->menuLinkManager = $container->get('plugin.manager.menu.link');
    $instance->routeProvider = $container->get('router.route_provider');
    $instance->urlEntityExtractor = $container->get('url_entity.extractor');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    $langcode = $options['langcode'] ?? $this->languageManager->getCurrentLanguage()->getId();

    if (!$linkPlugin = $this->getMenuLinkFromData($data)) {
      return;
    }

    foreach ($tokens as $name => $original) {
      if ($name === 'id') {
        continue;
      }

      if ($name === 'title') {
        $linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode);
        $dependencies->addEntity($linkEntity);
      }

      if ($name === 'url') {
        $linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode);
        $dependencies->addEntity($linkEntity);

        $referencedEntity = $this->getReferencedEntity($linkPlugin, $langcode);
        if ($referencedEntity && $alias = $this->getPathAliasByEntity($referencedEntity)) {
          $dependencies->addEntity($alias);
        }
      }

      if ($name === 'parent') {
        $this->addDependenciesByType('menu-link', ['menu-link:title' => NULL], ['menu-link' => $linkPlugin], $options, $dependencies);

        if ($parentId = $linkPlugin->getParent()) {
          $this->addDependenciesByType('menu-link', ['menu-link:title' => NULL], ['menu-link' => $this->getMenuLink($parentId)], $options, $dependencies);
        }
      }

      if ($name === 'parents') {
        $this->addDependenciesByType('menu-link', ['menu-link:title' => NULL], ['menu-link' => $linkPlugin], $options, $dependencies);

        foreach ($this->getMenuLinkParents($linkPlugin) as $parent) {
          $this->addDependenciesByType('menu-link', ['menu-link:title' => NULL], ['menu-link' => $parent], $options, $dependencies);
        }
      }

      if ($name === 'root' && $parents = $this->getMenuLinkParents($linkPlugin)) {
        $this->addDependenciesByType('menu-link', ['menu-link:title' => NULL], ['menu-link' => array_shift($parents)], $options, $dependencies);
      }
    }

    if ($parentId = $linkPlugin->getParent()) {
      if ($parentTokens = $this->tokens->findWithPrefix($tokens, 'parent')) {
        if ($linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode)) {
          $dependencies->addEntity($linkEntity);
        }

        $this->addDependenciesByType('menu-link', $parentTokens, ['menu-link' => $this->getMenuLink($parentId)], $options, $dependencies);
      }

      if ($rootTokens = $this->tokens->findWithPrefix($tokens, 'root')) {
        if ($parents = $this->getMenuLinkParents($linkPlugin)) {
          $this->addDependenciesByType('menu-link', $rootTokens, ['menu-link' => array_shift($parents)], $options, $dependencies);
        }
      }
    }

    if ($parentsTokens = $this->tokens->findWithPrefix($tokens, 'parents')) {
      if ($linkEntity = $this->getMenuLinkEntity($linkPlugin, $langcode)) {
        $dependencies->addEntity($linkEntity);
      }

      if ($parents = $this->getMenuLinkParents($linkPlugin)) {
        $this->addDependenciesByType('array', $parentsTokens, ['array' => $parents], $options, $dependencies);
      }
    }

    if ($urlTokens = $this->tokens->findWithPrefix($tokens, 'url')) {
      $this->addDependenciesByType('url', $urlTokens, ['url' => $linkPlugin->getUrlObject()], $options, $dependencies);
    }
  }

  /**
   * Get the menu link from the provided data.
   */
  protected function getMenuLinkFromData(array $data): ?MenuLinkInterface {
    $link = $data['menu-link'];

    if ($link instanceof MenuLinkInterface) {
      return $link;
    }

    if ($link instanceof MenuLinkContentInterface) {
      return $this->menuLinkManager->createInstance($link->getPluginId());
    }

    return NULL;
  }

  /**
   * Get the menu link parents.
   */
  protected function getMenuLinkParents(MenuLinkInterface $menuLink): array {
    $parentIds = $this->menuLinkManager->getParentIds($menuLink->getPluginId());
    unset($parentIds[$menuLink->getPluginId()]);

    return array_map(
      function (string $parentId) {
        return $this->getMenuLink($parentId);
      },
      $parentIds
    );
  }

  /**
   * Get the menu link plugin.
   */
  protected function getMenuLink(string $pluginId): MenuLinkInterface {
    return $this->menuLinkManager->createInstance($pluginId);
  }

}
