<?php

namespace Drupal\spotify_playing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Config;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

/**
 * Service to fetch API object from Spotify.
 */
class SpotifyHelper {

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $settings;

  /**
   * API Object.
   *
   * @var array|null[]
   */
  protected array $apiObject;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->settings = $config_factory->getEditable('spotify_playing.settings');
    $this->apiObject = [
      'api'     => NULL,
      'session' => NULL,
    ];
  }

  /**
   * API Object.
   */
  public function api(): SpotifyWebAPI {
    $this->fetchApiObject();
    return $this->apiObject['api'];
  }

  /**
   * Session Object.
   */
  public function session(): Session {
    $this->fetchApiObject();
    return $this->apiObject['session'];
  }

  /**
   * Perform the handshake to get the API Object and Session Object.
   */
  private function fetchApiObject(): void {
    $client_id = $this->settings->get('client_id');
    $client_secret = $this->settings->get('client_secret');

    if (!empty($client_id) && !empty($client_secret)) {
      $access_token = $this->settings->get('access_token');
      $refresh_token = $this->settings->get('refresh_token');

      $session = new Session(
      $client_id,
      $client_secret
      );

      if (!empty($access_token)) {
        $session->setAccessToken($access_token);
        $session->setRefreshToken($refresh_token);
      }
      else {
        $session->refreshAccessToken($refresh_token);
      }

      $options = [
        'auto_refresh' => TRUE,
      ];

      $api = new SpotifyWebAPI($options, $session);

      $api->setSession($session);
      $this->apiObject['api'] = $api;
      $this->apiObject['session'] = $session;

      $this->settings
        ->set('access_token', $session->getAccessToken())
        ->set('refresh_token', $session->getRefreshToken())
        ->save();
    }
  }

}
