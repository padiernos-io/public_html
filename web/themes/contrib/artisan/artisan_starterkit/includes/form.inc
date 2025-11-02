<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

/**
 * Implements hook_preprocess_form().
 */
function artisan_starterkit_preprocess_form(&$variables) {
  $variables['#attached']['library'][] = 'artisan_starterkit/shared-form';
}

/**
 * Implements hook_preprocess_webform().
 */
function artisan_starterkit_preprocess_webform(&$variables) {
  $variables['#attached']['library'][] = 'artisan_starterkit/shared-form';
}

/**
 * Implements hook_preprocess_form_element_label().
 */
function artisan_starterkit_preprocess_form_element_label(&$variables) {
  $variables['#element']['#attributes']['classes'][] = 'label';
  $variables['#element']['#attributes']['classes'][] = 'mb-3';
}

