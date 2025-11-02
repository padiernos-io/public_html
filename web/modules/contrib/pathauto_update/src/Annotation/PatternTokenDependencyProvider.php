<?php

namespace Drupal\pathauto_update\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Provides dependencies for tokens.
 *
 * @Annotation
 */
class PatternTokenDependencyProvider extends Plugin {

  /**
   * The type.
   */
  public string $type;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->definition['type'];
  }

}
