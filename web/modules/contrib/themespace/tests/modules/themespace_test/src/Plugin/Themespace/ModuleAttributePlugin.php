<?php

namespace Drupal\themespace_test\Plugin\Themespace;

use Drupal\Core\Plugin\PluginBase;
use Drupal\themespace_test\Attribute\ThemespaceTest;

/**
 * A basic plugin implementation from the test module.
 */
#[ThemespaceTest(
  id: 'module.test.attribute',
)]
class ModuleAttributePlugin extends PluginBase {
}
