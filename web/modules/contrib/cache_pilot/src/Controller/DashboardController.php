<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\cache_pilot\Cache\ApcuCache;
use Drupal\cache_pilot\Cache\OpcacheCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a controller for cache statistics dashboard.
 */
final readonly class DashboardController implements ContainerInjectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get(ApcuCache::class),
      $container->get(OpcacheCache::class),
    );
  }

  /**
   * Constructs a new DashboardController.
   */
  public function __construct(
    private ApcuCache $apcu,
    private OpcacheCache $opcache,
  ) {}

  /**
   * Builds a page title.
   *
   * @param string $cache_type
   *   The requested cache type.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   */
  public function pageTitle(string $cache_type): TranslatableMarkup {
    return match ($cache_type) {
      'apcu' => new TranslatableMarkup('APCu statistics'),
      'opcache' => new TranslatableMarkup('Zend OPcache statistics'),
      default => new TranslatableMarkup('Cache Pilot dashboard'),
    };
  }

  /**
   * Returns the cache statistics dashboard.
   *
   * @param string $cache_type
   *   The requested cache type.
   *
   * @return array
   *   The page contents.
   */
  public function __invoke(string $cache_type): array {
    $statistics = match ($cache_type) {
      'apcu' => $this->apcu->statistics(),
      'opcache' => $this->opcache->statistics(),
      default => NULL,
    };

    if (!$statistics) {
      return [
        '#markup' => new TranslatableMarkup('Looks like there is a problem with the connection to FastCGI.'),
      ];
    }

    return [
      '#theme' => match($cache_type) {
        'apcu' => 'cache_pilot_apcu_statistics',
        'opcache' => 'cache_pilot_opcache_statistics',
        default => throw new \InvalidArgumentException(\sprintf('Unexpected cache type %s', $cache_type)),
      },
      '#statistics' => $statistics,
    ];
  }

}
