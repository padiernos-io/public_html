<?php

namespace Drupal\spotify_playing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Configuration Form for the Spotify Now Playing module.
 */
class SpotifyConfigForm extends ConfigFormBase {

  const SETTINGS = 'spotify_playing.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'spotify_playing_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config(static::SETTINGS);

    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('Enter the Client ID of the Spotify Playing API client.'),
    ];

    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#required' => TRUE,
      '#default_value' => $config->get('client_secret'),
    ];

    $redirect_url = Url::fromRoute('spotify_playing.authorization', [], [
      'absolute' => TRUE,
    ])->toString();

    $form['redirect_url'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('<strong>Redirect URI:</strong> <a href="@redirect_url">@redirect_url</a> <br /> (Provide this URI to your Spotify App.)', [
        '@redirect_url' => $redirect_url,
      ]),
    ];

    if ($config->get('client_id') && $config->get('client_secret')) {

      $endpoint_test = Url::fromRoute('spotify_playing.endpoint', [], [
        'absolute' => TRUE,
      ])->toString();

      $form['endpoint_test'] = [
        '#type'  => 'html_tag',
        '#tag'   => 'p',
        '#value' => $this->t('<strong>Test the Endpoint:</strong> <a href="@endpoint_test">@endpoint_test</a> <br/> (Make sure you are playing a song.)', [
          '@endpoint_test' => $endpoint_test,
        ]),
      ];

    }

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $config->set('client_id', $form_state->getValue('client_id'));
    $config->set('client_secret', $form_state->getValue('client_secret'));

    $config->save();

    $route = Url::fromRoute('spotify_playing.authorization');
    $form_state->setRedirectUrl($route);

    parent::submitForm($form, $form_state);
  }

}
