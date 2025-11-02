<?php

/**
 * @file
 * Theme and preprocess functions for theme settings.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\artisan\ArtisanCustomizations;

/**
 * Implements hook_form_system_theme_settings_alter().
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
function artisan_form_system_theme_settings_alter(&$form, FormStateInterface $form_state, $form_id = NULL) {
  // Work-around for a core bug affecting admin themes. See issue #943212.
  if (isset($form_id)) {
    return;
  }
  $build_info = $form_state->getBuildInfo();
  ArtisanCustomizations::themeSettingsAlter($form, reset($build_info['args']));
}
