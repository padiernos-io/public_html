<?php

namespace Drupal\themespace\Plugin\Discovery;

use Drupal\Component\Discovery\DiscoverableInterface;
use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Component\Plugin\Discovery\DiscoveryTrait;
use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Allows plugin discovery from module and theme YAML files.
 *
 * YAML based discovery for plugins. Based on the Drupal Core YAML discovery
 * (\Drupal\Core\Plugin\Discovery\YamlDiscovery) with the addition that plugins
 * are aware of the extension type of the provider.
 *
 * Plugin added by this discovery object can support the
 * \Drupal\themespace\Plugin\Definition\ProviderTypedPlugin definition and
 * will populate "providerType" property on the plugin definition based on
 * if the plugin was discovered from a module or theme directory respectively.
 *
 * @see \Drupal\Core\Plugin\Discovery\YamlDiscovery
 */
class ProviderTypedYamlDiscovery implements ProviderTypedDiscoveryInterface {

  use DiscoveryTrait;

  /**
   * Fully namespaced class to create plugind definitions with.
   *
   * @var string|null
   */
  protected $pluginDefinitionClass = NULL;

  /**
   * YAML discovery instances for scanning theme and module directories.
   *
   * Discovery instances for set for theme and module discovery. The discovery
   * instances are keyed by "theme" or "module" and are only created if the
   * relevant directories were provided to the constructor.
   *
   * @var \Drupal\Component\Discovery\DiscoverableInterface[]
   */
  protected array $discovery = [];

  /**
   * Contains an array of properties to transform into translatable markup.
   *
   * Array key is the plugin definition property to translate. The array value
   * is the translation context if any.
   *
   * @var string[]
   */
  protected array $translatableProperties = [];

  /**
   * Construct a ProviderTypedYamlDiscovery object.
   *
   * @param string $name
   *   The file name suffix to use for discovery; for example, 'test' will
   *   search for "MODULE.test.yml" in each of the directories.
   * @param string[] $moduleDirectories
   *   Directories to look for YAML plugins files defined by modules.
   * @param string[] $themeDirectories
   *   Directories to look for YAML plugins files defined by themes.
   * @param string $pluginDefinitionClass
   *   The plugin definition class to use when generating plugin definitions. If
   *   empty, plugin definitions are returned as arrays.
   */
  public function __construct(string $name, array $moduleDirectories = [], array $themeDirectories = [], $pluginDefinitionClass = NULL) {
    // Only add module discovery if module directories are provided.
    if (!empty($moduleDirectories)) {
      $this->discovery['module'] = new YamlDiscovery($name, $moduleDirectories);
    }

    // Only add theme discovery if theme directories are provided.
    if (!empty($themeDirectories)) {
      $this->discovery['theme'] = new YamlDiscovery($name, $themeDirectories);
    }

    if ($pluginDefinitionClass) {
      $this->setPluginDefinitionClass($pluginDefinitionClass);
    }
  }

  /**
   * Sets the plugin definition class to use when discovering definitions.
   *
   * @param class-string $pluginDefinitionClass
   *   Class to set as the plugin definition class.
   */
  public function setPluginDefinitionClass(string $pluginDefinitionClass): void {
    $this->pluginDefinitionClass = $pluginDefinitionClass;
  }

  /**
   * Set of the YAML defined properties as being translatable.
   *
   * @param string $valueKey
   *   The property key to make translatable.
   * @param string $contextKey
   *   The property key to use as the translation context if there is one.
   *
   * @return $this
   */
  public function addTranslatableProperty(string $valueKey, string $contextKey = ''): ProviderTypedYamlDiscovery {
    $this->translatableProperties[$valueKey] = $contextKey;
    return $this;
  }

  /**
   * Creates the expected plugin definition class.
   *
   * If self::$pluginDefinitionClass is set to a class, build an instance of
   * that class from the array definition. Otherwise, just return the array
   * back as it was originally defined.
   *
   * Should be overridden if the plugin class definition constructor requires
   * different parameters.
   *
   * @param array $definition
   *   Plugin definition from the YAML file.
   *
   * @return \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array
   *   The plugin definition as of the appropriate class, or the original
   *   definition array if no class is set for the plugin definition.
   */
  protected function createDefinition(array $definition): PluginDefinitionInterface|array {
    if ($this->pluginDefinitionClass) {
      return new $this->pluginDefinitionClass($definition);
    }

    return $definition;
  }

  /**
   * Builds the translatable properties for plugin definitions.
   *
   * Method makes properties defined in static::$translatableProperties
   * translatable.
   *
   * @param array $definition
   *   The raw plugin definition from the YAML file.
   *
   * @return array
   *   Updated definition with translatable properties wrapped in a
   *   \Drupal\Core\StringTranslation\TranslatableMarkup instance.
   */
  protected function buildTranslatableProperties(array $definition): array {
    foreach ($this->translatableProperties as $property => $contextKey) {
      if (isset($definition[$property])) {
        $options = [];

        if ($contextKey && !empty($definition[$contextKey])) {
          $options['context'] = $definition[$contextKey];
          unset($definition[$contextKey]);
        }

        // Property is from static YAML files and should be scannable for
        // translation from there? This is also how this is done for
        // Drupal\Core\Plugin\Discovery\YamlDiscovery so unless a better way
        // is managed there, this is a valid use of non-literal string for t().
        // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
        $definition[$property] = new TranslatableMarkup($definition[$property], [], $options);
      }
    }

    return $definition;
  }

  /**
   * Find all plugin definition using the providered discovery object.
   *
   * @param \Drupal\Component\Discovery\DiscoverableInterface $discovery
   *   The discovery object to use to find definitions.
   * @param string $providerType
   *   The provider type to apply to each of the plugin definitions. Should be
   *   either "module" or "theme".
   *
   * @return array
   *   Discovered plugin definitions found using the discovery object provided.
   */
  protected function findDefinitions(DiscoverableInterface $discovery, string $providerType): array {
    $definitions = [];

    foreach ($discovery->findAll() as $provider => $plugins) {
      foreach ($plugins as $id => $definition) {
        $definition = $this->buildTranslatableProperties($definition);
        $definitions[$id] = $this->createDefinition($definition + [
          'id' => $id,
          'provider' => $provider,
          'providerType' => $providerType,
        ]);
      }
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    $definitions = [];

    foreach ($this->discovery as $providerType => $discovery) {
      $definitions += $this->findDefinitions($discovery, $providerType);
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDefinitions(): array {
    if (isset($this->discovery['module'])) {
      return $this->findDefinitions($this->discovery['module'], 'module');
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeDefinitions(): array {
    if (isset($this->discovery['theme'])) {
      return $this->findDefinitions($this->discovery['theme'], 'theme');
    }
    return [];
  }

}
