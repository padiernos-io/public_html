<?php

namespace Drupal\cookieinformation\Plugin\Block;

use Drupal\cookieinformation\CookieInformationBlockBase;

/**
 * Provides a 'Cookieinformation Privacy Controls Block.
 *
 * @Block(
 *   id = "cookieinformation_privacy_controls_block",
 *   admin_label = @Translation("Cookieinformation Privacy Controls block"),
 *   category = @Translation("Cookieinformation"),
 * )
 */
class PrivacyControlsBlock extends CookieInformationBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="cicc-template"></div>',
    ];
  }

}
