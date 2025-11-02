<?php

/**
 * @file
 * Theme and preprocess functions for blocks.
 */

 /**
 * Implements hook_preprocess_HOOK() for case 'blocks'.
 */
function artisan_starterkit_preprocess_block(&$variables) {
  if ($variables['plugin_id'] == 'vlsuite_block_inline_block:chart') {
    $block = $variables['elements']['content']['#block_content'];
    if ($block->hasField('field_chart_data') && !$block->get('field_chart_data')->isEmpty()) {
      $variables['chart_data'] = json_encode($block->get('field_chart_data')->value);
    } else {
      $variables['chart_data'] = '{"labels": [], "datasets": []}';
    }
    if ($block->hasField('field_chart_type') && !$block->get('field_chart_type')->isEmpty()) {
      $variables['chart_type'] = $block->get('field_chart_type')->value;
    }
  }
}
