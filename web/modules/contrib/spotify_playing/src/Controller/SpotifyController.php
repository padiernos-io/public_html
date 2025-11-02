<?php

namespace Drupal\spotify_playing\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\spotify_playing\SpotifyHelper;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Config;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Processing and output for Spotify API data.
 */
class SpotifyController implements ContainerInjectionInterface {

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
   * Constructs a new SpotifyController object.
   */
  public function __construct(SpotifyHelper $spotify, ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->spotify = $spotify;
    $this->settings = $config_factory->getEditable(static::SETTINGS);
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * JSON Endpoint for Spotify Now Playing widgets.
   */
  public function content() : CacheableJsonResponse {

    $api = $this->spotify->api();

    $data = [];

    if ($api) {
      $data['now'] = json_decode(json_encode($api->getMyCurrentTrack()), TRUE) ?? [];

      if ($this->request->query->has('reduce')) {
        $data = $this->reduceData($data);
      }
    }

    $data['#cache'] = [
      'max-age'  => (1 * 5),
      'tags'     => [
        'spotify_playing',
        'spotify_playing:now',
      ],
      'contexts' => [
        'url.path',
        'url.query_args',
      ],
    ];

    $response = new CacheableJsonResponse($data);

    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));

    return $response;

  }

  /**
   * Reduce the API response to only critical data required for display.
   *
   * @param array $data
   *   API response array.
   *
   * @return array
   *   Reduced data.
   */
  private function reduceData(array $data) : array {

    if (!empty($data['now'])) {

      // Remove top level items we don't need.
      unset($data['now']['timestamp']);
      unset($data['now']['context']);
      unset($data['now']['currently_playing_type']);
      unset($data['now']['actions']);

      // Item information.
      unset($data['now']['item']['available_markets']);
      unset($data['now']['item']['disc_number']);
      unset($data['now']['item']['explicit']);
      unset($data['now']['item']['external_ids']);
      unset($data['now']['item']['is_local']);
      unset($data['now']['item']['popularity']);
      unset($data['now']['item']['preview_url']);
      unset($data['now']['item']['track_number']);
      unset($data['now']['item']['type']);

      // Album information.
      unset($data['now']['item']['album']['album_type']);
      unset($data['now']['item']['album']['available_markets']);
      unset($data['now']['item']['album']['external_urls']);
      unset($data['now']['item']['album']['release_date']);
      unset($data['now']['item']['album']['release_date_precision']);
      unset($data['now']['item']['album']['total_tracks']);
      unset($data['now']['item']['album']['type']);

      // Artist information.
      foreach ($data['now']['item']['artists'] as &$artist) {
        unset($artist['external_urls']);
        unset($artist['type']);
      }

    }

    return $data;

  }

}
