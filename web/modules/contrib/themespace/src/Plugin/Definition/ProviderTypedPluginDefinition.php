<?php

namespace Drupal\themespace\Plugin\Definition;

use Drupal\Component\Plugin\Definition\DerivablePluginDefinitionInterface;
use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;

/**
 * Base class for plugin definitions which know their provider's extension type.
 */
class ProviderTypedPluginDefinition extends PluginDefinition implements ProviderTypedPluginDefinitionInterface {

  /**
   * The extension type of the plugin provider ('module' or 'theme').
   *
   * @var string
   */
  protected string $providerType;

  /**
   * Create a new instance of the PreprocessDefinition class.
   *
   * @param array $definition
   *   The plugin definition values to set for this definition.
   *
   * @see \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(array $definition) {
    if (empty($definition['provider']) || empty($definition['providerType'])) {
      $msg = sprintf('Plugin "%s" definition is missing provider, or provider type. Make sure you are using a ProviderTypedPluginInterface attribute or annotation.', $definition['id']);
      throw new InvalidPluginDefinitionException($msg);
    }

    // If plugin definition supports deriver definitions the set the deriver.
    // Tempted to enforce that "class" is not empty, but a plugin manager
    // might provider a default fallback class to use, meaning it can be valid
    // that "class" could be empty here.
    if (!empty($definition['deriver']) && $this instanceof DerivablePluginDefinitionInterface) {
      $this->setDeriver($definition['deriver']);
      unset($definition['deriver']);
    }
    elseif (empty($definition['class'])) {
      $msg = sprintf('Plugin "%s" definition is missing class.', $definition['id']);
      throw new InvalidPluginDefinitionException($msg);
    }

    if (isset($definition['class'])) {
      $this->setClass($definition['class']);
      unset($definition['class']);
    }

    // Apply class properties which were defined by the class.
    foreach ($definition as $key => $value) {
      if (property_exists(static::class, $key)) {
        $propMeta = new \ReflectionProperty(static::class, $key);

        // Ensure to never accidentally try to assign static properties as
        // this is likely to be unintentional.
        if (!$propMeta->isStatic()) {
          $this->{$key} = $value;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProviderType(): ?string {
    return $this->providerType;
  }

}
