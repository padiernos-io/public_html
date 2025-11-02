<?php

namespace Drupal\themespace_test_theme\Plugin\Themespace;

use Drupal\Core\Plugin\PluginBase;
use Drupal\themespace_test\Attribute\ThemespaceTest;

/**
 * A basic plugin implementation from the test theme.
 */
#[ThemespaceTest(
  id: 'theme.test.attribute',
)]
class ThemeAttributePlugin extends PluginBase {
}
