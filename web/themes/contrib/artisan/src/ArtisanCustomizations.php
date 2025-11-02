<?php

namespace Drupal\artisan;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\UrlHelper;

/**
 * Artisan customizations.
 */
class ArtisanCustomizations implements ArtisanCustomizationsInterface {

  /**
   * {@inheritdoc}
   */
  public static function getActive(): bool {
    return theme_get_setting('customizations') ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getFonts(): string {
    $fonts_storage = theme_get_setting('fonts') ?? '';
    $fonts_decoded = !empty($fonts_storage) ? Json::decode($fonts_storage) ?? [] : [];
    $declared_fonts = [];
    foreach ($fonts_decoded as $font_definition) {
      if (!empty($font_definition['font_face'])) {
        $declared_fonts[] = $font_definition['font_face'];
      }
    }
    return implode('', $declared_fonts);
  }

  /**
   * {@inheritdoc}
   */
  public static function getProperties(): string {
    $properties_storage = theme_get_setting('properties') ?? '';
    $properties_decoded = !empty($properties_storage) ? Json::decode($properties_storage) ?? [] : [];
    $declared_defult_properties = [];
    $declared_dark_mode_properties = [];
    foreach ($properties_decoded as $property_definition) {
      if (!empty($property_definition['property']) && !empty($property_definition['value'])) {
        $declared_defult_properties[] = $property_definition['property'] . ':' . $property_definition['value'] . ';';
      }
      if (!empty($property_definition['property']) && !empty($property_definition['dark_mode_value'])) {
        $declared_dark_mode_properties[] = $property_definition['property'] . ':' . $property_definition['dark_mode_value'] . ';';
      }
    }
    $default_properties = !empty($declared_defult_properties) ? static::PROPERTIES_DEFAULT_SELECTOR . ' {' . implode('', $declared_defult_properties) . '}' : '';
    $dark_mode_properties = !empty($declared_dark_mode_properties) ? static::PROPERTIES_DARK_MODE_SELECTOR . ' {' . implode('', $declared_dark_mode_properties) . '}' : '';
    return $default_properties . PHP_EOL . $dark_mode_properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function pageAttachmentsAlter(&$attachments): void {
    $font_customizations = self::getFonts();
    $attachments['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'style',
        '#value' => $font_customizations,
        '#access' => self::getActive() && !empty($font_customizations),
        '#attributes' => ['data-artisan-customizations-fonts' => 'enabled'],
      ],
      'artisan_customizations_fonts',
    ];
    $properties_customizations = self::getProperties();
    $attachments['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'style',
        '#value' => $properties_customizations,
        '#access' => self::getActive() && !empty($properties_customizations),
        '#attributes' => ['data-artisan-customizations-properties' => 'enabled'],
      ],
      'artisan_customizations_properties',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function themeSettingsAlter(&$form, string $theme): void {
    // Skip for main theme, these options should only appear for subthemes.
    if ($theme == 'artisan') {
      $form['info'] = [
        '#markup' => t('IMPORTANT: Go to your subtheme settings to customize your theme. Or use "artisan_starterkit" as a preview.'),
      ];
      return;
    }
    // Vite dev URL configuration.
    $request = \Drupal::request();
    $form['vite_dev_host'] = [
      '#title' => t('Vite: Dev host'),
      '#type' => 'url',
      '#default_value' => theme_get_setting('vite_dev_host', $theme),
      '#description' => t('It will allow hot module replacement at vite dev mode. Default: @default', [
        '@default' => sprintf('%s://%s', $request->getScheme(), $request->getHost()),
      ]),
    ];

    $form['vite_dev_port'] = [
      '#title' => t('Vite: Dev port'),
      '#type' => 'url',
      '#default_value' => theme_get_setting('vite_dev_port', $theme),
      '#description' => t('It will allow hot module replacement at vite dev mode. Default: 3000'),
    ];

    $form['customizer'] = [
      '#type' => 'details',
      '#title' => t('Artisan - Customizations'),
      '#description' => t('Adjust each theme available option of your choice. List is automatically generated based into your current css variables / properties, add more to expose here.'),
      '#weight' => -999,
      '#open' => TRUE,
    ];
    $form['customizer']['customizations'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable customizations'),
      '#default_value' => theme_get_setting('customizations', $theme) ?? FALSE,
      '#description' => t('Check this to enable or disable customizations. Use this to keep & avoid any active customization to be applied.'),
    ];
    $form['customizer']['fonts'] = [
      '#type' => 'textarea',
      '#title' => t('Fonts storage'),
      '#default_value' => theme_get_setting('fonts', $theme) ?? '',
      '#attributes' => [
        'class' => ['artisan-fonts-storage'],
      ],
    ];
    $form['customizer']['properties'] = [
      '#type' => 'textarea',
      '#title' => t('Properties storage'),
      '#default_value' => theme_get_setting('properties', $theme) ?? '',
      '#attributes' => [
        'class' => ['artisan-properties-storage'],
      ],
    ];

    $form['customizer']['app'] = [
      '#type' => 'html_tag',
      '#tag' => 'artisan-customizer',
      '#attributes' => [
        'cssPropertiesStorageSelector' => ".artisan-properties-storage",
        'cssFontDefinitionsStorageSelector' => ".artisan-fonts-storage",
        'mainCssAssets' => json_encode(self::getMainCssAssets()),
        'componentsCssAssets' => json_encode(self::getComponentsCssAssets()),
      ],
    ];

    $form['#attached']['library'][] = 'artisan/ui';
  }

  /**
   * Get the css assets from main theme library.
   *
   * @return array
   *   List.
   */
  public static function getMainCssAssets() {
    $default_theme = \Drupal::config('system.theme')->get('default');
    $main_libraries = \Drupal::service('library.discovery')->getLibrariesByExtension($default_theme) ?? [];
    $css_assets = [];
    foreach ($main_libraries as $main_library) {
      foreach ($main_library['css'] ?? [] as $main_library_css) {
        if (!empty($main_library_css['data'])) {
          $css_asset_realpath = UrlHelper::isExternal($main_library_css['data']) ? $main_library_css['data'] : realpath($main_library_css['data']);
          $css_assets[] = UrlHelper::isExternal($main_library_css['data']) ? $main_library_css['data'] : base_path() . str_replace(DRUPAL_ROOT . '/', '', $css_asset_realpath);
        }
      }
    }
    return $css_assets;
  }

  /**
   * Get the css assets from theme components.
   *
   * @return array
   *   List of paths of css files, relative to the theme.
   */
  protected static function getComponentsCssAssets() {
    /** @var \Drupal\Core\Theme\ComponentPluginManager */
    $sdc_plugin_manager = \Drupal::service('plugin.manager.sdc');
    /** @var \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery */
    $library_discovery = \Drupal::service('library.discovery');
    /** @var \Drupal\Core\Extension\ExtensionList $theme_extensions */
    $theme_extensions = \DrupaL::service('extension.list.theme');
    $css_assets = [];
    $default_theme = \Drupal::config('system.theme')->get('default');
    foreach ($sdc_plugin_manager->getAllComponents() as $component) {
      [$plugin_extension] = explode(':', $component->getPluginId());
      // Exclude contrib themes not managed by artisan.
      if ($theme_extensions->exists($plugin_extension) && !in_array($plugin_extension, [
        $default_theme,
        'artisan',
        'core',
      ])) {
        continue;
      }

      [$component_extension, $library_name] = explode('/', $component->getLibraryName());
      $component_library = $library_discovery->getLibraryByName($component_extension, $library_name);
      $component_library_css = $component_library['css'] ?? [];
      foreach ($component_library_css as $component_library_css_asset) {
        if (!empty($component_library_css_asset['data'])) {
          $css_asset_realpath = UrlHelper::isExternal($component_library_css_asset['data']) ? $component_library_css_asset['data'] : realpath($component_library_css_asset['data']);
          $css_assets[] = UrlHelper::isExternal($component_library_css_asset['data']) ? $component_library_css_asset['data'] : base_path() . str_replace(DRUPAL_ROOT . '/', '', $css_asset_realpath);
        }
      }
    }

    return $css_assets;
  }

}
