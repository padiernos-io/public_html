<?php

namespace Drupal\themespace_test\Attribute;

use Drupal\themespace\Attribute\ProviderTypedPlugin;
use Drupal\themespace_test\Plugin\TestPluginDefinition;

/**
 * Test plugin attribute for checking plugin attribute discovery.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ThemespaceTest extends ProviderTypedPlugin {

  /**
   * {@inheritdoc}
   */
  public function get(): array|object {
    return new TestPluginDefinition(parent::get());
  }

}
