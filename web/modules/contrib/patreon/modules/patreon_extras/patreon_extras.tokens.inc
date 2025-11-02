<?php

/**
 * @file
 * Token support for the Patreon Extras module.
 */

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function patreon_extras_token_info(): array {
  $info['pledge_count'] = [
    'name' => t('Pledge Count'),
    'description' => t('The number of pledges currently made.'),
  ];
  $info['pledge_amount'] = [
    'name' => t('Pledge Amount'),
    'description' => t('The current amount of pledges in $USD'),
  ];
  return [
    'types' => [
      'patreon' => [
        'name' => t('Patreon'),
        'description' => t('Tokens related to Patreon data.'),
      ],
    ],
    'tokens' => [
      'patreon' => $info,
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function patreon_extras_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata): array {
  $replacements = [];
  $sanitize = !empty($options['sanitize']);
  if ($type == 'patreon') {
    $config = \Drupal::config('patreon_extras.settings');

    // If we've never pulled in data, try it now.
    if ($config->get('patreon_extras_pledge_data_last_update') == 0) {
      patreon_extras_update_pledge_data();
    }

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'pledge_count':
          $replacements[$original] = $config->get('patreon_extras_pledge_count');
          break;

        case 'pledge_amount':
          $stored = $config->get('patreon_extras_pledge_amount');
          $replacements[$original] = ($stored == 0) ? '$0.00' : '$' . $stored / 100;
          break;

      }
    }

    if ($sanitize) {
      foreach ($replacements as $token => $value) {
        $replacements[$token] = Xss::filter($value);
      }
    }
  }

  return $replacements;
}
