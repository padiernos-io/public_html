<?php

namespace Drupal\themespace\Plugin;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinitionInterface;

/**
 * Plugin manager functionality to support provider typed plugins.
 *
 * Trait supports the addition of provider typed plugins for plugin managers
 * that support theme and module plugins. Provider typed plugin definitions
 * are aware of the provider's extension type (plugin came from module or
 * theme).
 *
 * Plugin definition annotations should implement
 * \Drupal\themespace\Annotation\ProviderTypedPluginInterface and optionally
 * be a plugin definition of a class which implements
 * \Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinitionInterface.
 *
 * The trait can be used with the \Drupal\Core\Plugin\DefaultPluginManager
 * plugin manager class and can help classes implement
 * \Drupal\themespace\Plugin\ProviderTypePluginManagerInterface.
 *
 * @see \Drupal\Core\Plugin\DefaultPluginManager
 * @see \Drupal\themespace\Annotation\ProviderTypedPluginInterface
 * @see \Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinitionInterface
 * @see \Drupal\themespace\Plugin\ProviderTypedPluginManagerInterface
 */
trait ProviderTypedPluginManagerTrait {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|null
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface|null
   */
  protected $themeHandler;

  /**
   * Plugin definitions provided by modules, and keyed by the plugin ID.
   *
   * So definitions are $this->moduleDefinitions[<plugin_id>] = definition.
   *
   * @var array|null
   *
   * @see self::groupPluginDefinitions()
   */
  protected ?array $moduleDefinitions;

  /**
   * Plugin definitions provided by themes.
   *
   * The plugin definitions are grouped by providing themes, so that each key
   * to the array is the providing theme name. The array values are an array
   * of plugin plugin definitions, keyed by the plugin ID.
   *
   * So elements are $this->themeDefinitions[<theme>][<plugin_id>] = definition.
   *
   * @var array[]|null
   *
   * @see self::groupPluginDefinitions()
   */
  protected ?array $themeDefinitions;

  /**
   * Gets the definition of all plugins for this type.
   *
   * @return array
   *   An array of plugin definitions (empty array if no definitions were
   *   found). Keys are plugin IDs.
   *
   * @see \Drupal\Component\Plugin\PluginManagerInterface::getDefinitions()
   * @see \Drupal\Core\Plugin\DefaultPluginManager::getDefinitions()
   */
  abstract public function getDefinitions();

  /**
   * Get the theme handler.
   *
   * @return \Drupal\Core\Extension\ThemeHandlerInterface
   *   The theme handler object.
   */
  protected function getThemeHandler(): ThemeHandlerInterface {
    if (!isset($this->themeHandler)) {
      $this->themeHandler = \Drupal::service('theme_handler');
    }

    return $this->themeHandler;
  }

  /**
   * Get the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler object.
   */
  protected function getModuleHandler(): ModuleHandlerInterface {
    if ($this->moduleHandler) {
      $this->moduleHandler = \Drupal::service('module_handler');
    }

    return $this->moduleHandler;
  }

  /**
   * Gets the plugin discovery.
   *
   * Should be implemented by the plugin manager to get a provider typed
   * plugin discovery object.
   *
   * @return \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   *   The plugin discovery object.
   *
   * @see \Drupal\Core\Plugin\PluginManagerBase::getDiscovery()
   * @see \Drupal\themespace\Discovery\ProviderTypedAnnotatedClassDiscovery
   * @see \Drupal\themespace\Discovery\ProviderTypedYamlDiscovery
   * @see \Drupal\themespace\Discovery\ProviderTypedYamlDiscoveryDecorator
   */
  abstract protected function getDiscovery(): DiscoveryInterface;

  /**
   * Extracts the provider from a plugin definition.
   *
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return string|null
   *   The provider string, if it exists. NULL otherwise.
   *
   * @see \Drupal\Core\Plugin\PluginManagerBase::extractProviderFromDefinition()
   */
  abstract protected function extractProviderFromDefinition($plugin_definition);

  /**
   * Performs extra processing on plugin definitions.
   *
   * Allows plugin managers to apply default or alter discovered plugin
   * definitions.
   *
   * @param mixed $definition
   *   The discovered plugin definition to process.
   * @param string $plugin_id
   *   The plugin ID.
   *
   * @see \Drupal\Core\Plugin\PluginManagerBase::processDefinition()
   */
  abstract public function processDefinition(&$definition, string $plugin_id);

  /**
   * Invokes the hook to alter the definitions if the alter hook is set.
   *
   * @param mixed[]|\Drupal\Component\Plugin\Definition\PluginDefinitionInterface[] $definitions
   *   The discovered plugin definitions.
   */
  abstract protected function alterDefinitions(&$definitions);

  /**
   * Determines if the provider of a definition exists.
   *
   * @param string $provider
   *   The name of the provider to look for.
   * @param string|null $providerType
   *   A string to determine which provider type to check. Generally should be
   *   either "module" or "theme".
   *
   * @return bool
   *   TRUE if the provider exists, or FALSE otherwise.
   *
   * @see \Drupal\Core\Plugin\DefaultPluginManager::providerExists()
   */
  protected function providerExists($provider, ?string $providerType = 'module') {
    if (in_array($provider, ['core', 'component'])) {
      return TRUE;
    }

    // Strictly check the module handler vs the theme handler, base on the
    // $provider_type as it is important that we only allow plugins that which
    // know they come from themes, to be validated by the theme handler.
    //
    // These plugins need to only be active when the theme and so the rest of
    // the plugin manager needs to know these plugins are identified properly.
    switch ($providerType) {
      case 'theme':
        return $this->getThemeHandler()->themeExists($provider);

      case 'module':
      default:
        return $this->getModuleHandler()->moduleExists($provider);
    }
  }

  /**
   * Extracts the provider's extension type from a plugin definition.
   *
   * @param array|\Drupal\Component\Plugin\Definition\PluginDefinitionInterface $plugin_definition
   *   The plugin definition.
   *
   * @return string|null
   *   A string of either "module" or "theme" if able to determine the
   *   extension type of the provider. NULL otherwise.
   */
  protected function extractProviderTypeFromDefinition(array|PluginDefinitionInterface $plugin_definition): ?string {
    if ($plugin_definition instanceof ProviderTypedPluginDefinitionInterface) {
      return $plugin_definition->getProviderType();
    }

    // Attempt to convert the plugin definition to an array.
    if (is_object($plugin_definition)) {
      $plugin_definition = (array) $plugin_definition;
    }

    if (isset($plugin_definition['providerType'])) {
      return $plugin_definition['providerType'];
    }

    return NULL;
  }

  /**
   * Finds and processes plugin definitions from the plugin discovery.
   *
   * Method is expected to override the default plugin manager method and adds
   * the "provider_type" key to the plugin definition if it can be determined
   * automatically.
   *
   * @return \Drupal\Component\Plugin\Definition\PluginDefinitionInterface[]
   *   Plugins discovered by this plugin manager keyed by the plugin ID.
   *
   * @see \Drupal\Core\Plugin\DefaultPluginManager::findDefinitions()
   * @see self::extractProviderFromDefinition()
   * @see self::extractProviderInfoFromDefinition()
   */
  protected function findDefinitions(): array {
    $definitions = $this
      ->getDiscovery()
      ->getDefinitions();

    foreach ($definitions as $plugin_id => &$definition) {
      $this->processDefinition($definition, $plugin_id);
    }

    // Allow definitions to be altered if supported by the plugin manager.
    $this->alterDefinitions($definitions);

    // If this plugin was provided by a module or theme that does not exist,
    // remove the plugin definition.
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $provider = $this->extractProviderFromDefinition($plugin_definition);
      $type = $this->extractProviderTypeFromDefinition($plugin_definition);

      if ($provider && !$this->providerExists($provider, $type)) {
        unset($definitions[$plugin_id]);
      }
    }

    return $definitions;
  }

  /**
   * Group the plugin definitions by the provider extension type.
   *
   * Method populates the classes self::$themeDefinitions and
   * self::moduleDefinitions with the preprocess plugins base on if they are
   * theme or module provided respectively.
   */
  protected function groupPluginDefinitions(): void {
    $this->moduleDefinitions = [];
    $this->themeDefinitions = [];

    foreach ($this->getDefinitions() as $pluginId => $definition) {
      switch ($this->extractProviderTypeFromDefinition($definition)) {
        case 'theme':
          $provider = $this->extractProviderFromDefinition($definition);
          $this->themeDefinitions[$provider][$pluginId] = $definition;
          break;

        case 'module':
        default:
          $this->moduleDefinitions[$pluginId] = $definition;
          break;
      }
    }
  }

  /**
   * Get plugin definitions that are provided by modules.
   *
   * @return \Drupal\Component\Plugin\Definition\PluginDefinitionInterface[]
   *   An array of plugin definitions that are provided by modules. Definitions
   *   are keyed by their plugin IDs.
   */
  public function getModuleDefinitions(): array {
    if (!isset($this->moduleDefinitions)) {
      $this->groupPluginDefinitions();
    }

    return $this->moduleDefinitions;
  }

  /**
   * Get plugin definitions provided by the requested theme.
   *
   * @param string $theme
   *   Machine name of theme to fetch the plugin definitions for.
   *
   * @return \Drupal\Component\Plugin\Definition\PluginDefinitionInterface[]
   *   An array of plugin definitions that were provided by the requested theme.
   *   Definitions are keyed by their plugin IDs. An empty array can be returned
   *   if there were no plugins provided by the requested theme.
   */
  public function getDefinitionsByTheme(string $theme): array {
    if (!isset($this->themeDefinitions)) {
      $this->groupPluginDefinitions();
    }

    // Find the base themes of the requested theme to ensure that preprocess
    // plugins defined by base themes are also included.
    $themeHandler = $this->getThemehandler();
    $activeThemes = $themeHandler->listInfo();
    $baseThemes = array_keys($themeHandler->getBaseThemes($activeThemes, $theme));
    $baseThemes[] = $theme;

    $definitions = [];
    foreach ($baseThemes as $themeName) {
      if (!empty($this->themeDefinitions[$themeName])) {
        $definitions += $this->themeDefinitions[$themeName];
      }
    }

    return $definitions;
  }

}
