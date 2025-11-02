<?php

namespace Drupal\spotify_playing\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\spotify_playing\SpotifyHelper;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Handles Authorization for Spotify API.
 */
class SpotifyAuthController implements ContainerInjectionInterface {

  /**
   * Settings key.
   */
  const SETTINGS = 'spotify_playing.settings';

  /**
   * Spotify Helper service.
   *
   * @var \Drupal\spotify_playing\SpotifyHelper
   */
  protected $spotify;

  /**
   * Configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $settings;

  /**
   * The Request Stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('spotify_playing.spotify_helper'),
      $container->get('config.factory'),
      $container->get('request_stack')
    );
  }

  /**
   * Constructs a new SpotifyAuthController object.
   */
  public function __construct(SpotifyHelper $spotify, ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->spotify = $spotify;
    $this->settings = $config_factory->getEditable(static::SETTINGS);
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * Validate and dump API/Session information.
   */
  public function content() : CacheableJsonResponse {

    $client_id = $this->settings->get('client_id');
    $client_secret = $this->settings->get('client_secret');

    $session_object = $this->spotify
      ->session();

    $dump = [
      'client_id'     => $client_id,
      'client_secret' => $client_secret,
      'access_token'  => $session_object->getAccessToken(),
      'refresh_token' => $session_object->getRefreshToken(),
    ];

    $response = new CacheableJsonResponse($dump);

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($dump));

    return $response;

  }

  /**
   * Authorization Callback route for API.
   */
  public function authorize() : void {

    $current_request = $this->request;

    $client_id = $this->settings->get('client_id');
    $client_secret = $this->settings->get('client_secret');

    $form_uri = Url::fromRoute('spotify_playing.settings')->toString();
    $redirect_uri = Url::fromRoute('spotify_playing.authorization', [], [
      'absolute' => TRUE,
    ])->toString();

    if (!empty($client_id) && !empty($client_secret)) {
      $code = $current_request->query->get('code');

      $session = new Session(
        $client_id,
        $client_secret,
        $redirect_uri
      );

      $api = new SpotifyWebAPI([
        'auto_refresh' => TRUE,
      ]);

      if (!empty($code)) {
        try {
          $session->requestAccessToken($code);
          $api->setAccessToken($session->getAccessToken());

          $this->settings
            ->set('access_token', $session->getAccessToken())
            ->set('refresh_token', $session->getRefreshToken())
            ->save();

          header('Location: ' . $redirect_uri);
        }
        catch (Exception $e) {
          echo 'Spotify API Error: ' . $e->getCode();
          die();
        }
      }

      $accessToken = $this->settings->get('access_token');

      if (empty($accessToken)) {
        $this->settings
          ->set('access_token', '')
          ->set('refresh_token', '')
          ->save();

        $authorizeUrlOptions = [
          'scope' => [
            'user-read-currently-playing',
            'user-read-recently-played',
            'user-read-playback-state',
            'user-modify-playback-state',
          ],
        ];

        header('Location: ' . $session->getAuthorizeUrl($authorizeUrlOptions));
        die();
      }

    }

    header('Location: ' . $form_uri);
    die();
  }

}
