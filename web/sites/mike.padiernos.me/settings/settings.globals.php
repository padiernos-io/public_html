<?php

/**
 * @file Provide custom Drupal settings.
 *
 * This file is the first extension point for settings.php.
 * See settings.includes.php for reference on the includes layout.
 *
 * If you're willing to set any setting already declared in settings.sbitio.php
 * please consider using environment variables or overriding it in
 * settings.sbitio-overrides.php.
 */

$settings['trusted_host_patterns'] = [
  '^mike+\.padiernos\.me$',
];
$settings['config_sync_directory'] = '/home/padiernos/public_html/sites/mike/config/sync';
$settings['file_private_path']     = '/home/padiernos/public_html/sites/mike/files/private';
$settings['file_temp_path']        = '/home/padiernos/public_html/sites/mike/files/temp';
