<?php

declare(strict_types=1);

namespace Drupal\spotify_playing\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides a Now Playing block.
 *
 * @Block(
 *  id = "spotify_now_playing_block",
 *     admin_label = @Translation("Spotify Now Playing"),
 *     category = @Translation("Custom")
 * )
 */
class SpotifyNowPlayingBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'spotify_playing_block',
      '#display_art' => $this->configuration['display_art'],
      '#endpoint' => Url::fromRoute('spotify_playing.endpoint')->toString(),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'display_art' => FALSE,
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['display_art'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display Album Artwork'),
      '#default_value' => $this->configuration['display_art'],
      '#description' => $this->t('Display Album Artwork.'),
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['display_art'] = $form_state->getValue('display_art');
  }

}
