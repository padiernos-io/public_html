<?php

namespace Drupal\themespace_test_subtheme\Plugin\Themespace;

use Drupal\Core\Plugin\PluginBase;
use Drupal\themespace_test\Attribute\ThemespaceTest;

/**
 * A basic plugin implementation from the test theme.
 */
#[ThemespaceTest(
  id: 'subtheme.test.attribute',
)]
class SubthemeAttributePlugin extends PluginBase {
}
