<?php

/**
 * Application Name (app_name).
 *
 * We use this value as a prefix for some settings. See settings.sbitio.php.
 */
// Use a name as short as possible and unique across the infrastructure.
$app_name = getenv('APP_NAME') ?: 'drupal';

/**
 * Application Environment (app_env).
 *
 * We use this value along with app_name to get a unique app+env identifier
 * we use for several settings below.
 *
 * app_env can be set in several ways. This is the precedence:
 *
 * 1. Obtain from environment variable set in the webserver / php processor
 * 2. Guess from domain names following APP_ENV.dev.DOMAIN.TLD pattern
 * 3. Set it with custom code in settings.custom.php
 *
 * If not set or guesses, the default app_env is "pro".
 */
// Check environment variables.
$app_env = getenv('APP_ENV');
if (!$app_env) {
  if (getenv('IS_DDEV_PROJECT') == 'true') {
    $app_env = 'local';
  }
  else {
    // Guess app_env from the domain name.
    // We accept APP_ENV.dev.DOMAIN.TLD and default to "pro" APP_ENV.
    list($host, ) = explode(':', $_SERVER['HTTP_HOST']);
    $dc = explode('.', $host);
    if (count($dc) > 1 && $dc[1] == 'dev') {
      $app_env = array_shift($dc);
    }
    else {
      $app_env = 'pro';
    }
  }
}
