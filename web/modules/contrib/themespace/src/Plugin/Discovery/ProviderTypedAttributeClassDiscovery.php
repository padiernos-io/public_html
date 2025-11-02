<?php

namespace Drupal\themespace\Plugin\Discovery;

use Drupal\Component\Plugin\Attribute\AttributeInterface;
use Drupal\Component\Plugin\Discovery\AttributeClassDiscovery;
use Drupal\themespace\Attribute\ProviderTypedPlugin;
use Drupal\themespace\NamespacesInterface;

/**
 * Attribute class discovery for plugins which are aware of the provider type.
 *
 * A plugin discovery handler for plugin attributes which are aware of their
 * provider's extension type. For theme provided plugins, is it important
 * for plugin managers and consumers to be aware these are provided by themes
 * and should only be used when that theme is active.
 *
 * @see \Drupal\themespace\Attribute\ProviderTypedPluginInterface
 */
class ProviderTypedAttributeClassDiscovery extends AttributeClassDiscovery implements ProviderTypedDiscoveryInterface {

  const NS_PROVIDER_REGEX = '#^Drupal\\\\(?<provider>[\w]+)(?:\\\\|$)#';

  /**
   * The full root namespaces to traverse for discovery.
   *
   * This should never be altered or used outside of setting an iterator for use
   * with the static::getPluginNamespaces() method, and is used to target module
   * or theme namespaces for the discovery to traverse.
   *
   * @var \Traversable|null
   *
   * @see self::getDefinitions()
   * @see self::getModuleDefinitions()
   * @see self::getThemeDefinitions()
   */
  protected ?\Traversable $rootNsIterator;

  /**
   * An array of directory and namespace suffixes to search for plugins.
   *
   * @var array<string,string>
   */
  protected array $nsSuffixes;

  /**
   * Map provider names to a provider type.
   *
   * The plugin discovery handler needs to be able to determine the provider
   * type from namespace, or class attributes. Build this map of module and
   * theme namespaces from the iterator and make it available.
   *
   * @var array<string,string>|null
   */
  protected ?array $providerTypeMap;

  /**
   * Creates a new instance of the ProviderTypedAnnotatedClassDiscovery class.
   *
   * @param string|string[] $subdir
   *   Either the plugin's subdirectory, for example 'Plugin/views/filter', or
   *   empty string if plugins are located at the top level of the namespace.
   * @param \Traversable $allNamespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   *   If $subdir is not an empty string, it will be appended to each namespace.
   * @param string $pluginAttributeClass
   *   (optional) The name of the annotation that contains the plugin
   *   definition. Defaults to 'Drupal\Component\Attribute\Plugin'.
   */
  public function __construct(array|string $subdir, protected \Traversable $allNamespaces, $pluginAttributeClass = ProviderTypedPlugin::class) {
    parent::__construct([], $pluginAttributeClass);

    $this->nsSuffixes = [];
    if ($subdir) {
      foreach ((array) $subdir as $path) {
        if ('/' !== $path[0]) {
          // Prepend a directory separator to $path if needed.
          $path = '/' . $path;
        }

        $this->nsSuffixes[$path] = str_replace('/', '\\', $path);
      }
    }

    if ($allNamespaces instanceof NamespacesInterface) {
      $namespaces = [
        'module' => $allNamespaces->getModuleIterator(),
        'theme' => $allNamespaces->getThemeIterator(),
      ];

      $this->providerTypeMap = [];
      foreach ($namespaces as $type => $iter) {
        foreach ($iter as $ns => $dir) {
          if (preg_match(self::NS_PROVIDER_REGEX, $ns, $matches)) {
            $this->providerTypeMap[$matches['provider']] = $type;
          }
        }
      }
    }
    else {
      $this->providerTypeMap = NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getProviderFromNamespace(string $namespace): ?string {
    return preg_match(self::NS_PROVIDER_REGEX, $namespace, $matches)
      ? mb_strtolower($matches['provider']) : NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareAttributeDefinition(AttributeInterface $attribute, string $class): void {
    parent::prepareAttributeDefinition($attribute, $class);

    if (!$attribute->getProvider()) {
      $attribute->setProvider($this->getProviderFromNamespace($class));
    }
    if ($attribute instanceof ProviderTypedPlugin && !$attribute->getProviderType()) {
      $type = 'module';
      if ($this->providerTypeMap) {
        $type = $this->providerTypeMap[$attribute->getProvider()] ?? NULL;
      }
      $attribute->setProviderType($type);

    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions(): array {
    // Ensure this is reset before attempting to do any discovery.
    return $this->getModuleDefinitions() + $this->getThemeDefinitions();
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleDefinitions(): array {
    // Create an iterator that only traverses namespaces that match the Drupal
    // namespace pattern. We alter the $this->rootNamespacesIterator so it can
    // be use in the parent::getDefinitions().
    // This avoids having to reimplement
    // self::getDefinitions().
    $this->rootNsIterator = $this->allNamespaces instanceof NamespacesInterface
      ? $this->allNamespaces->getModuleIterator()
      : new \IteratorIterator($this->allNamespaces);

    $definitions = parent::getDefinitions();
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeDefinitions(): array {
    // @see self::getModuleDefinitions()
    if ($this->allNamespaces instanceof NamespacesInterface) {
      $this->rootNsIterator = $this->allNamespaces->getThemeIterator();
      $definitions = parent::getDefinitions();
      return $definitions;
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getPluginNamespaces(): array {
    $pluginNs = [];

    if ($this->nsSuffixes) {
      foreach ($this->rootNsIterator as $namespace => $dirs) {
        foreach ($this->nsSuffixes as $path => $ns) {
          $currentNs = $namespace . $ns;
          foreach ((array) $dirs as $dir) {
            // Append the directory suffix to the PSR-4 base directory, to
            // obtain the directory where plugins are found.
            $pluginNs[$currentNs][] = $dir . $path;
          }
        }
      }
    }
    else {
      // Both the namespace suffix and the directory suffix are empty,
      // so the plugin namespaces and directories are the same as the base
      // directories.
      foreach ($this->rootNsIterator as $namespace => $dirs) {
        $pluginNs[$namespace] = (array) $dirs;
      }
    }

    return $pluginNs;
  }

}
