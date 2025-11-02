<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Utils;

use Drupal\Core\StringTranslation\PluralTranslatableMarkup;

/**
 * Provides helpers for statistics.
 */
final readonly class StatisticsHelper {

  /**
   * Format the rate value per second.
   */
  public static function formatRate(int|float $rate): \Stringable {
    return new PluralTranslatableMarkup(
      (int) $rate,
      '@count_formatted cache request/second',
      '@count_formatted cache requests/second',
      ['@count_formatted' => self::formatNumber($rate, 2)],
    );
  }

  /**
   * Format number.
   */
  public static function formatNumber(int|float $number, int $decimals = 0): string {
    // The thousands separator is NNBSP.
    return number_format($number, $decimals, thousands_separator: ' ');
  }

}
