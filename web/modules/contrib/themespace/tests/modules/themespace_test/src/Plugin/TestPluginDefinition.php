<?php

namespace Drupal\themespace_test\Plugin;

use Drupal\Component\Plugin\Definition\DerivablePluginDefinitionInterface;
use Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinition;

/**
 * A plugin definition to test constructor property assignments with.
 */
class TestPluginDefinition extends ProviderTypedPluginDefinition implements DerivablePluginDefinitionInterface {

  /**
   * Class name to use as the definition deriver this plugin definition.
   *
   * @var string
   */
  public $deriverClass;

  /**
   * Test the assignment when implementing DerivablePluginInterface.
   *
   * Assignment should use the ::setDeriver() class so this value should
   * remain empty, and only ::$deriverClass should be populated.
   *
   * @var string
   */
  public $deriver;

  /**
   * {@inheritdoc}
   */
  public function getDeriver() {
    return $this->deriverClass;
  }

  /**
   * {@inheritdoc}
   */
  public function setDeriver($deriver) {
    $this->deriverClass = $deriver;
    return $this;
  }

}
