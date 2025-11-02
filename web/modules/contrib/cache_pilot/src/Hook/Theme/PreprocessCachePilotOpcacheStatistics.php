<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Hook\Theme;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\cache_pilot\Utils\StatisticsHelper;

/**
 * Preprocess cache_pilot_opcache_statistics.
 *
 * @see theme_preprocess_cache_pilot_opcache_statistics()
 */
final readonly class PreprocessCachePilotOpcacheStatistics {

  /**
   * Implements hook_preprocess_HOOK().
   */
  public function __invoke(array &$variables): void {
    $this->prepareGeneralInformation($variables);
    $this->prepareStringsInformation($variables);
    $this->prepareOpcacheStatsInformation($variables);
  }

  /**
   * Prepares general statistic information.
   */
  private function prepareGeneralInformation(array &$variables): void {
    $statistics = $variables['statistics'];
    $total_memory = $statistics['memory_usage']['used_memory'] + $statistics['memory_usage']['free_memory'];

    $variables['general'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'data' => new TranslatableMarkup('General Information'),
          'colspan' => 2,
        ],
      ],
      '#rows' => [
        [
          'name' => new TranslatableMarkup('Enabled'),
          'value' => $statistics['opcache_enabled'] ? new TranslatableMarkup('Yes') : new TranslatableMarkup('No'),
        ],
        [
          'name' => new TranslatableMarkup('Cache full'),
          'value' => $statistics['cache_full'] ? new TranslatableMarkup('Yes') : new TranslatableMarkup('No'),
        ],
        [
          'name' => new TranslatableMarkup('Restart pending'),
          'value' => $statistics['restart_pending'] ? new TranslatableMarkup('Yes') : new TranslatableMarkup('No'),
        ],
        [
          'name' => new TranslatableMarkup('Restart in progress'),
          'value' => $statistics['restart_in_progress'] ? new TranslatableMarkup('Yes') : new TranslatableMarkup('No'),
        ],
        [
          'name' => new TranslatableMarkup('Memory used'),
          'value' => ByteSizeMarkup::create($statistics['memory_usage']['used_memory']),
        ],
        [
          'name' => new TranslatableMarkup('Memory free'),
          'value' => ByteSizeMarkup::create($statistics['memory_usage']['free_memory']),
        ],
        [
          'name' => new TranslatableMarkup('Memory wasted'),
          'value' => ByteSizeMarkup::create($statistics['memory_usage']['wasted_memory']),
        ],
        [
          'name' => new TranslatableMarkup('Used & Wasted & Free'),
          'value' => [
            'data' => [
              '#theme' => 'cache_pilot_fragmentation_bar',
              '#fragments' => [
                [
                  'value' => ByteSizeMarkup::create($statistics['memory_usage']['used_memory']),
                  'percentage' => round($statistics['memory_usage']['used_memory'] / $total_memory * 100, 2),
                  'label' => new TranslatableMarkup('Used'),
                  'color' => '#DD2C00',
                ],
                [
                  'value' => ByteSizeMarkup::create($statistics['memory_usage']['wasted_memory']),
                  'percentage' => round($statistics['memory_usage']['wasted_memory'] / $total_memory * 100, 2),
                  'label' => new TranslatableMarkup('Wasted'),
                  'color' => '#FFAB00',
                ],
                [
                  'value' => ByteSizeMarkup::create($statistics['memory_usage']['free_memory']),
                  'percentage' => round($statistics['memory_usage']['free_memory'] / $total_memory * 100, 2),
                  'label' => new TranslatableMarkup('Free'),
                  'color' => '#64DD17',
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Prepares strings statistic information.
   */
  private function prepareStringsInformation(array &$variables): void {
    $statistics = $variables['statistics'];

    $variables['strings'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'data' => new TranslatableMarkup('Strings Information'),
          'colspan' => 2,
        ],
      ],
      '#rows' => [
        [
          'name' => new TranslatableMarkup('Strings buffer size'),
          'value' => ByteSizeMarkup::create($statistics['interned_strings_usage']['buffer_size']),
        ],
        [
          'name' => new TranslatableMarkup('Strings memory used'),
          'value' => ByteSizeMarkup::create($statistics['interned_strings_usage']['used_memory']),
        ],
        [
          'name' => new TranslatableMarkup('Strings memory free'),
          'value' => ByteSizeMarkup::create($statistics['interned_strings_usage']['free_memory']),
        ],
        [
          'name' => new TranslatableMarkup('Number of strings'),
          'value' => StatisticsHelper::formatNumber($statistics['interned_strings_usage']['number_of_strings']),
        ],
        [
          'name' => new TranslatableMarkup('Used & Free'),
          'value' => [
            'data' => [
              '#theme' => 'cache_pilot_fragmentation_bar',
              '#fragments' => [
                [
                  'value' => ByteSizeMarkup::create($statistics['interned_strings_usage']['used_memory']),
                  'percentage' => round($statistics['interned_strings_usage']['used_memory'] / $statistics['interned_strings_usage']['buffer_size'] * 100, 2),
                  'label' => new TranslatableMarkup('Used'),
                  'color' => '#DD2C00',
                ],
                [
                  'value' => ByteSizeMarkup::create($statistics['interned_strings_usage']['free_memory']),
                  'percentage' => round($statistics['interned_strings_usage']['free_memory'] / $statistics['interned_strings_usage']['buffer_size'] * 100, 2),
                  'label' => new TranslatableMarkup('Free'),
                  'color' => '#64DD17',
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Prepares OPcache stats information.
   */
  private function prepareOpcacheStatsInformation(array &$variables): void {
    $statistics = $variables['statistics'];
    $opcache_stats = $statistics['opcache_statistics'];
    $total_hits = $opcache_stats['hits'] + $opcache_stats['misses'] + $opcache_stats['blacklist_misses'];

    $variables['strings'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'data' => new TranslatableMarkup('OPcache Stats Information'),
          'colspan' => 2,
        ],
      ],
      '#rows' => [
        [
          'name' => new TranslatableMarkup('Cached scripts'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['num_cached_scripts']),
        ],
        [
          'name' => new TranslatableMarkup('Cached keys'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['num_cached_scripts']),
        ],
        [
          'name' => new TranslatableMarkup('Max cached keys'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['max_cached_keys']),
        ],
        [
          'name' => new TranslatableMarkup('Start time'),
          'value' => DrupalDateTime::createFromTimestamp($opcache_stats['start_time']),
        ],
        [
          'name' => new TranslatableMarkup('Last restart time'),
          'value' => $opcache_stats['last_restart_time'] ? DrupalDateTime::createFromTimestamp($opcache_stats['last_restart_time']) : new TranslatableMarkup('Never'),
        ],
        [
          'name' => new TranslatableMarkup('OOM restarts'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['oom_restarts']),
        ],
        [
          'name' => new TranslatableMarkup('Hash restarts'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['hash_restarts']),
        ],
        [
          'name' => new TranslatableMarkup('Manual restarts'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['manual_restarts']),
        ],
        [
          'name' => new TranslatableMarkup('Hits'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['hits']),
        ],
        [
          'name' => new TranslatableMarkup('Misses'),
          'value' => StatisticsHelper::formatNumber($opcache_stats['misses']),
        ],
        [
          'name' => new TranslatableMarkup('Blacklist misses (%)'),
          'value' => new FormattableMarkup('@misses (@ratio%)', [
            '@misses' => StatisticsHelper::formatNumber($opcache_stats['blacklist_misses']),
            '@ratio' => round($opcache_stats['blacklist_miss_ratio'], 2),
          ]),
        ],
        [
          'name' => new TranslatableMarkup('Opcache hit rate'),
          'value' => round($opcache_stats['opcache_hit_rate'], 2),
        ],
        [
          'name' => new TranslatableMarkup('Hits & Blacklist & Misses'),
          'value' => [
            'data' => [
              '#theme' => 'cache_pilot_fragmentation_bar',
              '#fragments' => [
                [
                  'value' => StatisticsHelper::formatNumber($opcache_stats['hits']),
                  'percentage' => round($opcache_stats['hits'] / $total_hits * 100, 2),
                  'label' => new TranslatableMarkup('Hits'),
                  'color' => '#64DD17',
                ],
                [
                  'value' => StatisticsHelper::formatNumber($opcache_stats['blacklist_misses']),
                  'percentage' => round($opcache_stats['blacklist_misses'] / $total_hits * 100, 2),
                  'label' => new TranslatableMarkup('Blacklist'),
                  'color' => '#FFAB00',
                ],
                [
                  'value' => StatisticsHelper::formatNumber($opcache_stats['misses']),
                  'percentage' => round($opcache_stats['misses'] / $total_hits * 100, 2),
                  'label' => new TranslatableMarkup('Misses'),
                  'color' => '#DD2C00',
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

}
