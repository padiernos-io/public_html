<?php

namespace Drupal\artisan;

/**
 * Artisan libraries.
 */
class ArtisanLibraries {

  /**
   * Theme to process vite libraries.
   *
   * @var string
   */
  protected $theme;

  /**
   * Theme path.
   *
   * @var string
   */
  protected $themePath;

  /**
   * Development URL.
   *
   * @var string
   */
  protected $devUrl;

  /**
   * Development mode active or not.
   *
   * @var bool
   */
  protected $devMode;

  /**
   * Vite manifest.
   *
   * @var array
   */
  protected $manifest;

  /**
   * Builds Artisan Libraries.
   *
   * @param string $theme
   *   Theme to process its libraries.
   */
  public function __construct(string $theme) {
    $this->theme = $theme;
    $this->themePath = \Drupal::service('extension.path.resolver')->getPath('theme', $this->theme);
    $this->initDevUrl();
    $this->initDevMode();
    $this->initManifest();
  }

  /**
   * Alter libraries.
   *
   * @param array $libraries
   *   Libraries.
   * @param string $extension
   *   Current extension.
   */
  public function alter(array &$libraries, $extension):void {
    $manifest = $this->getManifest();
    if (!empty($manifest) && $this->devMode()) {
      $is_allowed_extension = in_array($extension, [$this->getTheme()]);
      foreach ($libraries as $library_id => &$library) {
        if (str_starts_with($library_id, 'components.') || $is_allowed_extension) {
          $this->alterJsAssets($library, $manifest);
          $this->alterCssAssets($library, $manifest);
        }
      }
    }

    if ($extension == $this->getTheme() && $this->devMode()) {
      $libraries['style']['js'][$this->getDevUrl() . '/' . $this->getThemePath() . '/dist/@vite/client'] = [
        'type' => 'external',
        'attributes' => ['type' => 'module'],
      ];
    }
  }

  /**
   * Alter javascript library asssets.
   *
   * @param array $library
   *   All current libraries.
   */
  protected function alterJsAssets(array &$library):void {
    if (!empty($library['js'])) {
      $this->alterAssets($library['js']);
    }
  }

  /**
   * Alter css library asssets.
   *
   * @param array $library
   *   All current libraries.
   */
  protected function alterCssAssets(array &$library):void {
    if (!empty($library['css'])) {
      foreach ($library['css'] as &$css_group) {
        $this->alterAssets($css_group);
      }
    }
  }

  /**
   * Alter library asssets.
   *
   * @param array $library_elements
   *   Library elements.
   */
  protected function alterAssets(array &$library_elements):void {
    $manifest = $this->getManifest();
    foreach ($library_elements as $path => $attributes) {
      // Gets the path that we should search into manifest.
      // Here we can receive a relative path from drupal root, or the relative path from
      // current theme.
      preg_match(sprintf('#.*%s/(?<path>.*)#', $this->getThemePath()), $path, $theme_matches);
      $path_candidate = $theme_matches['path'] ?? $path;
      $path_candidate = str_replace('dist/', '', $path_candidate);

      // When the path exists,
      // replace the library with the path and the included imports.
      foreach ($manifest as $info) {
        if ($info['file'] == $path_candidate && !empty($info['src'])) {
          unset($library_elements[$path]);
          $attributes['minified'] = TRUE;
          $attributes['type'] = 'external';
          $dev_url = sprintf(
            '%s/%s/%s',
            $this->getDevUrl(),
            $this->getThemePath(),
            $info['src'],
          );
          $library_elements[$dev_url] = $attributes;
          break;
        }
      }
    }
  }

  /**
   * Get development URL.
   *
   * @return string
   */
  public function getDevUrl():string {
    return $this->devUrl;
  }

  /**
   * Development mode.
   *
   * @return bool
   *   Dev mode avtive or not.
   */
  public function devMode():bool {
    return $this->devMode;
  }

  /**
   * Get Vite manifest.
   *
   * @return array
   *   Manifest data.
   */
  public function getManifest():array {
    return $this->manifest;
  }

  /**
   * Get theme path.
   *
   * @return string
   *   Theme path.
   */
  public function getThemePath():string {
    return $this->themePath;
  }

  /**
   * Get theme.
   *
   * @return string
   *   Theme.
   */
  public function getTheme():string {
    return $this->theme;
  }

  /**
   * Init development url.
   */
  protected function initDevUrl():void {
    $vite_dev_host = theme_get_setting('vite_dev_host', $this->getTheme());
    $vite_dev_port = theme_get_setting('vite_dev_port', $this->getTheme()) ?? 3000;
    if (empty($vite_dev_host)) {
      $current_request = \Drupal::request();
      $vite_dev_host = sprintf('%s://%s', $current_request->getScheme(), $current_request->getHost());
    }
    $this->devUrl = $vite_dev_host . ':' . $vite_dev_port;
  }

  /**
   * Init vite manifest.
   */
  protected function initManifest():void {
    $path = sprintf('%s/dist/.vite/manifest.json', $this->getThemePath());
    if (file_exists($path)) {
      $this->manifest = json_decode((string) file_get_contents($path), TRUE) ?? [];
    }
    else {
      $this->manifest = [];
    }
  }

  /**
   * Init development mode when Vite node task is detected.
   */
  protected function initDevMode():void {
    $dev_url = $this->getDevUrl();
    if (!empty($dev_url)) {
      try {
        $this->devMode = \Drupal::httpClient()->get($dev_url)->getStatusCode() == 200;
      }
      catch (\Exception $exc) {
        // Regular mode, do nothing, vite dev process must be running.
        $this->devMode = FALSE;
      }
    }
  }

}
