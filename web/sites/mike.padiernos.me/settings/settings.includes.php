<?php

include $app_root . '/' . $site_path . '/settings/settings.init.php';
include $app_root . '/' . $site_path . '/settings/settings.globals.php';

if (file_exists($app_root . '/' . $site_path . '/settings/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.local.php';
}

if (file_exists($app_root . '/' . $site_path . '/settings/settings.deployment.php')) {
  include $app_root . '/' . $site_path . '/settings/settings.deployment.php';
}
