<?php

namespace Drupal\themespace\Plugin;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\themespace\Plugin\Discovery\ProviderTypedAttributeClassDiscovery;

/**
 * A default plugin manager for supporting the provider typed plugins.
 *
 * Meant to be a replacement for \Drupal\Core\Plugin\DefaultPluginManager when
 * the plugin manager needs to support separation module and theme plugins.
 * Implementers are responsible for determining how to apply their theme plugins
 * based on which theme is active.
 *
 * Some plugins (like preprocessors) can be applied to and cached in the
 * hook_theme_alter() per active theme, and getting the theme plugins with
 * the self::getDefinitionsByTheme() is sufficient. This may not work for some
 * plugin use cases so be careful!
 *
 * @phpstan-ignore drupal.pluginManagerInspection.alterPlugin
 */
class ProviderTypedPluginManager extends DefaultPluginManager implements ProviderTypedPluginManagerInterface {

  use ProviderTypedPluginManagerTrait;

  /**
   * Create a new instance of the ProviderTypedPluginManager class.
   *
   * @param string[]|string|bool $subdir
   *   The plugin's subdirectory, for example Plugin/views/filter. An array can
   *   be used with the ProviderTypedAttributeClassDiscovery discovery class.
   *   For any other discovery types, only use a string or a boolean TRUE.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler.
   * @param string|null $pluginInterface
   *   The interface each plugin should implement.
   * @param string|null $pluginDefinitionAttributeName
   *   The name of the attribute that contains the plugin definition. Defaults
   *   to 'Drupal\themespace\Attribute\ProviderTypedPlugin'. If the class
   *   provided is an Annotation, for backwards compatibility this gets applied
   *   to the annotation value.
   *
   * @see \Drupal\themespace\Plugin\Discovery\ProviderTypedAttributeClassDiscovery
   * @see \Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscovery
   */
  public function __construct(
    array|string|bool $subdir,
    \Traversable $namespaces,
    ModuleHandlerInterface $moduleHandler,
    ThemeHandlerInterface $themeHandler,
    ?string $pluginInterface = NULL,
    ?string $pluginDefinitionAttributeName = NULL,
  ) {
    parent::__construct(
      $subdir,
      $namespaces,
      $moduleHandler,
      $pluginInterface,
      $pluginDefinitionAttributeName,
    );

    $this->themeHandler = $themeHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function clearCachedDefinitions(): void {
    parent::clearCachedDefinitions();

    // If definition caches have been cleared, also remove our currently
    // grouped plugin definitions.
    unset($this->moduleDefinitions);
    unset($this->themeDefinitions);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery(): DiscoveryInterface {
    if (!$this->discovery) {
      $discovery = new ProviderTypedAttributeClassDiscovery(
        $this->subdir,
        $this->namespaces,
        $this->pluginDefinitionAttributeName
      );

      $this->discovery = new ContainerDerivativeDiscoveryDecorator($discovery);
    }

    return $this->discovery;
  }

}
