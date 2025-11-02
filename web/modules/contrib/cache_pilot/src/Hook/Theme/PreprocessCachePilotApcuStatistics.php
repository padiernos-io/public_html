<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Hook\Theme;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\cache_pilot\Utils\StatisticsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Preprocess cache_pilot_apcu_statistics.
 *
 * @see theme_preprocess_cache_pilot_apcu_statistics()
 */
final readonly class PreprocessCachePilotApcuStatistics implements ContainerInjectionInterface {

  public function __construct(
    private TimeInterface $time,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get(TimeInterface::class),
    );
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  public function __invoke(array &$variables): void {
    $this->prepareGeneralCacheInformation($variables);
    $this->prepareMemoryInformation($variables);
  }

  /**
   * Prepares general cache information.
   */
  private function prepareGeneralCacheInformation(array &$variables): void {
    $cache_info = $variables['statistics']['cache_info'];
    $variables['cache'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'data' => new TranslatableMarkup('Cache Information'),
          'colspan' => 2,
        ],
      ],
      '#rows' => [
        [
          'name' => new TranslatableMarkup('Start time'),
          'value' => DrupalDateTime::createFromTimestamp($cache_info['start_time']),
        ],
        [
          'name' => new TranslatableMarkup('Cached Variables'),
          'value' => $this->getCachedVariables($cache_info),
        ],
        [
          'name' => new TranslatableMarkup('Hits'),
          'value' => StatisticsHelper::formatNumber($cache_info['num_hits']),
        ],
        [
          'name' => new TranslatableMarkup('Misses'),
          'value' => StatisticsHelper::formatNumber($cache_info['num_misses']),
        ],
        [
          'name' => new TranslatableMarkup('Request Rate (hits, misses)'),
          'value' => $this->getRequestRate($cache_info),
        ],
        [
          'name' => new TranslatableMarkup('Hit Rate'),
          'value' => $this->getHitRate($cache_info),
        ],
        [
          'name' => new TranslatableMarkup('Miss Rate'),
          'value' => $this->getMissRate($cache_info),
        ],
        [
          'name' => new TranslatableMarkup('Insert Rate'),
          'value' => $this->getInsertRate($cache_info),
        ],
        [
          'name' => new TranslatableMarkup('Cache full count'),
          'value' => $cache_info['expunges'],
        ],
        [
          'name' => new TranslatableMarkup('Hits & Misses'),
          'value' => [
            'data' => [
              '#theme' => 'cache_pilot_fragmentation_bar',
              '#fragments' => [
                [
                  'value' => StatisticsHelper::formatNumber($cache_info['num_hits']),
                  'percentage' => round($cache_info['num_hits'] / ($cache_info['num_misses'] + $cache_info['num_hits']) * 100, 2),
                  'label' => new TranslatableMarkup('Hits'),
                  'color' => '#64DD17',
                ],
                [
                  'value' => StatisticsHelper::formatNumber($cache_info['num_misses']),
                  'percentage' => round($cache_info['num_misses'] / ($cache_info['num_misses'] + $cache_info['num_hits']) * 100, 2),
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

  /**
   * Prepares memory information.
   */
  private function prepareMemoryInformation(array &$variables): void {
    $cache_info = $variables['statistics']['cache_info'];
    $memory_info = $variables['statistics']['memory_info'];
    $total_memory = $memory_info['seg_size'] * $memory_info['num_seg'];

    $variables['memory'] = [
      '#type' => 'table',
      '#header' => [
        'name' => [
          'data' => new TranslatableMarkup('Memory Information'),
          'colspan' => 2,
        ],
      ],
      '#rows' => [
        [
          'name' => new TranslatableMarkup('Memory type'),
          'value' => $cache_info['memory_type'],
        ],
        [
          'name' => new TranslatableMarkup('Number of segments'),
          'value' => $memory_info['num_seg'],
        ],
        [
          'name' => new TranslatableMarkup('Segment size'),
          'value' => ByteSizeMarkup::create($memory_info['seg_size']),
        ],
        [
          'name' => new TranslatableMarkup('Used memory'),
          'value' => ByteSizeMarkup::create($cache_info['mem_size']),
        ],
        [
          'name' => new TranslatableMarkup('Available memory'),
          'value' => ByteSizeMarkup::create($memory_info['avail_mem']),
        ],
        [
          'name' => new TranslatableMarkup('Used & Available'),
          'value' => [
            'data' => [
              '#theme' => 'cache_pilot_fragmentation_bar',
              '#fragments' => [
                [
                  'value' => ByteSizeMarkup::create($cache_info['mem_size']),
                  'percentage' => round($cache_info['mem_size'] / $total_memory * 100, 2),
                  'label' => new TranslatableMarkup('Used'),
                  'color' => '#DD2C00',
                ],
                [
                  'value' => ByteSizeMarkup::create($memory_info['avail_mem']),
                  'percentage' => round(($total_memory - $cache_info['mem_size']) / $total_memory * 100, 2),
                  'label' => new TranslatableMarkup('Available'),
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
   * Return formatted cached variables.
   */
  private function getCachedVariables(array $statistics): \Stringable {
    return new FormattableMarkup('@entries (@size_vars)', [
      '@entries' => StatisticsHelper::formatNumber($statistics['num_entries']),
      '@size_vars' => ByteSizeMarkup::create($statistics['mem_size']),
    ]);
  }

  /**
   * Return formatted request rate.
   */
  private function getRequestRate(array $statistics): \Stringable {
    if ($statistics['num_hits']) {
      $rate = ($statistics['num_hits'] + $statistics['num_misses']) / max($this->time->getRequestTime() - $statistics['start_time'], 1);
    }
    else {
      $rate = 0;
    }

    return StatisticsHelper::formatRate($rate);
  }

  /**
   * Return formatted hit rate.
   */
  private function getHitRate(array $statistics): \Stringable {
    if ($statistics['num_hits']) {
      $rate = $statistics['num_hits'] / max($this->time->getRequestTime() - $statistics['start_time'], 1);
    }
    else {
      $rate = 0;
    }

    return StatisticsHelper::formatRate($rate);
  }

  /**
   * Return formatted miss rate.
   */
  private function getMissRate(array $statistics): \Stringable {
    if ($statistics['num_misses']) {
      $rate = $statistics['num_misses'] / max($this->time->getRequestTime() - $statistics['start_time'], 1);
    }
    else {
      $rate = 0;
    }

    return StatisticsHelper::formatRate($rate);
  }

  /**
   * Return formatted insert rate.
   */
  private function getInsertRate(array $statistics): \Stringable {
    if ($statistics['num_inserts']) {
      $rate = $statistics['num_inserts'] / max($this->time->getRequestTime() - $statistics['num_inserts'], 1);
    }
    else {
      $rate = 0;
    }

    return StatisticsHelper::formatRate($rate);
  }

}
