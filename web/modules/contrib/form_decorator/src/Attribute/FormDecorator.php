<?php

namespace Drupal\form_decorator\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * The FormDecorator attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class FormDecorator extends Plugin {

  /**
   * Constructs a FormDecorator attribute.
   *
   * @param string $hook
   *   The form alter hook.
   * @param int $weight
   *   The weight of the decorator (lower weights are applied earlier).
   */
  public function __construct(
    public readonly string $hook = '',
    public readonly int $weight = 0,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getId():string {
    return $this->getClass();
  }

}
