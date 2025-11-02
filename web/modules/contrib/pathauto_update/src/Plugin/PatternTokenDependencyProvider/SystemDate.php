<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;

/**
 * Provides dependencies for tokens with date formats.
 *
 * @PatternTokenDependencyProvider(
 *   type = "date",
 * )
 */
class SystemDate extends PatternTokenDependencyProviderBase {

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    foreach ($tokens as $token => $rawToken) {
      switch ($token) {
        case 'short':
        case 'medium':
        case 'long':
          $storage = $this->entityTypeManager->getStorage('date_format');
          $dateFormat = $storage->load($token);
          $dependencies->addEntity($dateFormat);
      }
    }
  }

}
