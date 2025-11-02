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
  '^padiernos\.me$',
  '^www+\.padiernos\.me$',
];
$settings['config_sync_directory']  = '/home/padiernos/public_html/sites/dev/config/sync';
$settings['file_private_path']      = '/home/padiernos/public_html/sites/dev/files/private';
$settings['file_temp_path']         = '/home/padiernos/public_html/sites/dev/files/temp';
