<?php

namespace Drupal\themespace\Plugin\Discovery;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;

/**
 * Decorates a plugin discovery object to add YAML plugin discovery.
 *
 * Adds plugin discovery for supporting definitions in YAML files. Plugins
 * added by this discovery object can support the
 * \Drupal\themespace\Plugin\Definition\ProviderTypedPlugin definition and
 * will populate "provider_type" property on the plugin definition based on
 * if the plugin was discovered from a module or theme directory respectively.
 *
 * @see \Drupal\Core\Plugin\Discovery\YamlDiscoveryDecorator
 */
class ProviderTypedYamlDiscoveryDecorator extends ProviderTypedYamlDiscovery {

  /**
   * The discovery object that is being decorated.
   *
   * @var \Drupal\Component\Plugin\Discovery\DiscoveryInterface
   */
  protected DiscoveryInterface $decorated;

  /**
   * Constructs a new ProviderTypedYamlDiscoveryDecorator object.
   *
   * @param \Drupal\Component\Plugin\Discovery\DiscoveryInterface $decorated
   *   The discovery object that is being decorated.
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
  public function __construct(DiscoveryInterface $decorated, $name, array $moduleDirectories = [], array $themeDirectories = [], $pluginDefinitionClass = NULL) {
    parent::__construct($name, $moduleDirectories, $themeDirectories, $pluginDefinitionClass);

    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    return parent::getDefinitions() + $this->decorated->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDefinitions(): array {
    if ($this->decorated instanceof ProviderTypedDiscoveryInterface) {
      return parent::getModuleDefinitions() + $this->decorated->getModuleDefinitions();
    }

    // If decorated is not provider type aware, treat all plugin definitions
    // as if they are "module" provided definitions.
    return parent::getModuleDefinitions() + $this->decorated->getDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeDefinitions(): array {
    if ($this->decorated instanceof ProviderTypedDiscoveryInterface) {
      return parent::getThemeDefinitions() + $this->decorated->getThemeDefinitions();
    }

    // If decorated is not provider type aware, treat all plugin definitions
    // as if they are "module" provided definitions and return only theme ones.
    return parent::getThemeDefinitions();
  }

  /**
   * Passes all calls for unknown methods onto the decorated discovery object.
   *
   * @param string $method
   *   Name of method being passed thru to the decorated discovery.
   * @param mixed $args
   *   Method arguments to pass to the method call.
   *
   * @return mixed
   *   Return value from the method invoked on the decorated object.
   */
  public function __call(string $method, mixed $args): mixed {
    return call_user_func_array([$this->decorated, $method], $args);
  }

}
