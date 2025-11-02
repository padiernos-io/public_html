<?php

/**
 * @file
 * Document API hooks.
 */

/**
 * Alter the render array used to produce an upgraded footnote tag in content.
 *
 * This applies to upgrading footnote content from 3x styles to 4x.
 *
 * @param array $build
 *   The render array representing the content of an individual footnote item.
 * @param array $context
 *   Options passed to the drush command.
 *
 * @see \Drupal\footnotes\Upgrade\FootnotesUpgradeBatchManager::replaceCallback()
 */
function hook_footnotes_upgrade_3x4x_build_alter(array &$build, array $context) {
  // Decode already-escaped HTML. (This is only an example to document how this
  // hook could be used; it is not necessary for all sites.)
  if (empty($context['use-data-text'])) {
    $build['#value'] = html_entity_decode($build['#value']);
  }
  else {
    $build['#attributes']['data-text'] = html_entity_decode($build['#attributes']['data-text']);
  }
}
