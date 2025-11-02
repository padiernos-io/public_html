<?php

/**
 * @file
 * Provides responses from FastCGI.
 *
 * @internal
 */

if (!array_key_exists('cache_pilot', $_SERVER) || $_SERVER['cache_pilot'] !== '1') {
  echo 'Request is not from the cache_pilot module.';
  exit;
}

/**
 * Returns whether APCu is enabled.
 */
function is_apcu_enabled(): bool {
  return extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOL);
}

/**
 * Returns whether Zend OPcache is enabled.
 */
function is_opcache_enabled(): bool {
  return extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOL);
}

// phpcs:ignore DrupalPractice.Variables.GetRequestData.SuperglobalAccessedWithVar
switch ($_POST['command'] ?? NULL) {
  case 'echo':
    echo 'Ok';
    break;

  case 'apcu-status':
    echo is_apcu_enabled() ? 'Ok' : 'APCu is not enabled';
    break;

  case 'apcu-clear':
    if (is_apcu_enabled()) {
      apcu_clear_cache();
      echo 'Ok';
    }
    else {
      echo 'APCu clear failed because APCu is not enabled';
    }
    break;

  case 'apcu-statistic':
    if (is_apcu_enabled()) {
      $statistics = [
        'cache_info' => apcu_cache_info(TRUE),
        'memory_info' => apcu_sma_info(),
      ];
    }
    else {
      $statistics = [];
    }
    echo json_encode($statistics);
    break;

  case 'opcache-status':
    echo is_opcache_enabled() ? 'Ok' : 'Zend Opcache is not enabled';
    break;

  case 'opcache-clear':
    if (is_opcache_enabled()) {
      opcache_reset();
      echo 'Ok';
    }
    else {
      echo 'Zend Opcache clear failed because Zend Opcache is not enabled';
    }
    break;

  case 'opcache-statistic':
    if (is_opcache_enabled()) {
      $statistics = opcache_get_status(FALSE);
    }
    else {
      $statistics = [];
    }
    echo json_encode($statistics);
    break;

  default:
    echo 'Unexpected command';
    break;
}
