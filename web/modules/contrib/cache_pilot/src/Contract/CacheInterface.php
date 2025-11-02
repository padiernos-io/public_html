<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Contract;

/**
 * Represents a cache type.
 */
interface CacheInterface {

  /**
   * Attempts to clear the cache.
   *
   * @return bool
   *   TRUE on success, FALSE otherwise.
   */
  public function clear(): bool;

  /**
   * Checks if cache type is enabled.
   *
   * @return bool
   *   TRUE if enabled, FALSE otherwise.
   */
  public function isEnabled(): bool;

  /**
   * Returns cache statistics.
   *
   * @return array
   *   An array of cache statistics.
   */
  public function statistics(): array;

}
