<?php

namespace Drupal\opcachectl\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * PHP OPcache reports.
 */
class OpcacheReportController extends ControllerBase {

  /**
   * Callback for the PHP OPcache statistics page.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function viewStatistics() {
    return [
      '#theme' => 'opcache_stats',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Callback for the PHP OPcache config dump page.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function viewConfig() {
    return [
      '#theme' => 'opcache_config',
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
