<?php

namespace Drupal\ept_hero\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Transform Block settings in CSS.
 */
class GenerateHeroCSS implements ContainerInjectionInterface {

  /**
   * The EPT Core configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new GenerateCSS object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get('ept_core.settings');
  }

  /**
   * Instantiates a new instance of this class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Generate CSS from $settings.
   */
  public function generateFromSettings($settings, $paragraph_class) {
    $hero_selector = '.' . $paragraph_class . ' .ept-hero-container';
    $col_1_selector = '.' . $paragraph_class . ' .ept-hero-container .hero-col-1';
    $col_2_selector = '.' . $paragraph_class . ' .ept-hero-container .hero-col-2';
    $hero_styles = '';

    $mobile_breakpoint = $settings['mobile_breakpoint'] ?? $this->config->get('ept_core_mobile_breakpoint');

    if (empty($mobile_breakpoint)) {
      $mobile_breakpoint = '480';
    }
    $mobile_breakpoint = str_replace('px', '', $mobile_breakpoint);

    $hero_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
    $hero_styles .= $col_1_selector . ' { width: 100%; } ';
    $hero_styles .= $col_2_selector . ' { width: 100%; } ';
    $hero_styles .= $hero_selector . ' { flex-direction: column; gap: 0; } ';
    $hero_styles .= ' } ';

    if (!empty($settings['image_position']) && $settings['image_position'] == 'right' &&
      ($settings['styles'] == 'two_columns')) {
      $hero_styles .= $col_1_selector . ' { order: 2; } ';
      $hero_styles .= $col_2_selector . ' { order: 1; } ';
    }

    if (!empty($settings['image_order_mobile']) && $settings['image_order_mobile'] == 'image_first') {
      $hero_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
      $hero_styles .= $col_1_selector . ' { order: 1; } ';
      $hero_styles .= $col_2_selector . ' { order: 2; } ';
      $hero_styles .= ' } ';
    }

    if (!empty($settings['image_order_mobile']) && $settings['image_order_mobile'] == 'image_last') {
      $hero_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
      $hero_styles .= $col_1_selector . ' { order: 2; margin-top: 20px; } ';
      $hero_styles .= $col_2_selector . ' { order: 1; } ';
      $hero_styles .= ' } ';
    }

    if (!empty($settings['overlay'])) {
      $hex = $settings['overlay_color'];
      $hex = ltrim($hex, '#');
      if (strlen($hex) === 3) {
        $hex = str_repeat($hex[0], 2) . str_repeat($hex[1], 2) . str_repeat($hex[2], 2);
      }
      $rgb = sscanf($hex, "%02x%02x%02x");

      $hero_styles .= '.' . $paragraph_class . ':after { ';
      $hero_styles .= ' background: rgba('. $rgb[0] . ',' . $rgb[1] .  ',' . $rgb[2] . ',' . $settings['overlay_alpha'] .'); ';
      $hero_styles .= ' content: ""; display: block; width: 100%; height: 100%; ';
      $hero_styles .= ' position: absolute; z-index: 1; left: 0; top: 0; z-index: 5; object-fit: cover;';
      $hero_styles .= ' } ';

      $hero_styles .= '.' . $paragraph_class . ' { ';
      $hero_styles .= ' position: relative; ';
      $hero_styles .= ' } ';

      $hero_styles .= '.' . $paragraph_class . ' .ept-container { ';
      $hero_styles .= ' position: relative; z-index: 6; ';
      $hero_styles .= ' } ';
    }

    return '<style>' . $hero_styles . '</style>';
  }

}
