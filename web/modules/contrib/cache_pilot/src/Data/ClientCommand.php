<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Data;

/**
 * List of available client commands.
 */
enum ClientCommand: string {

  // Used for connection status checks.
  case Echo = 'echo';
  case ApcuClear = 'apcu-clear';
  case ApcuStatus = 'apcu-status';
  case ApcuStatistic = 'apcu-statistic';
  case OpcacheClear = 'opcache-clear';
  case OpcacheStatus = 'opcache-status';
  case OpcacheStatistic = 'opcache-statistic';

}
