<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Exception;

/**
 * Exception thrown when the connection type is missing.
 */
final class MissingConnectionTypeException extends \RuntimeException {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('The FastCGI connection type is missing');
  }

}
