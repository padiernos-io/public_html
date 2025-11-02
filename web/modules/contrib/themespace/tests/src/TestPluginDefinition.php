<?php

namespace Drupal\Tests\themespace;

use Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinition;

/**
 * A plugin definition to test constructor property assignments with.
 */
class TestPluginDefinition extends ProviderTypedPluginDefinition {

  /**
   * Static value to test assignment safety of static values.
   *
   * Generally plugin definitions would not have static values, but one was
   * added here to ensure the safety of value assignment in the parent
   * constructor.
   *
   * @var string
   */
  public static $staticVal = 'original';

  /**
   * Standard and common property to assignment.
   *
   * @var \Drupal\Core\StringTranslation\TranslatableMarkup|string
   */
  public $label;

  /**
   * Test the assignment when not implementing DerivablePluginInterface.
   *
   * @var string
   */
  public $deriver;

  /**
   * A property with a default to test default remains intact.
   *
   * @var string
   */
  public $defaulted = 'default';

  /**
   * A class property to test definition constructor assignment.
   *
   * @var mixed
   */
  public $assignable;

}
