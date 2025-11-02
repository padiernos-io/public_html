<?php

namespace Drupal\ept_cta\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Transform Paragraph settings in CSS.
 */
class GenerateCtaCSS implements ContainerInjectionInterface {

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
  public function generateFromSettings($settings, $pagraph_class) {
    $cta_selector = '.' . $pagraph_class . ' .ept-paragraph-cta__content';
    $col_1_selector = '.' . $pagraph_class . ' .ept-paragraph-cta__content .column-1';
    $col_2_selector = '.' . $pagraph_class . ' .ept-paragraph-cta__content .column-2';
    $cta_styles = '';

    $mobile_breakpoint = $settings['mobile_breakpoint'];
    if (empty($mobile_breakpoint)) {
      $mobile_breakpoint = $this->config->get('ept_core_mobile_breakpoint');
    }

    if (empty($mobile_breakpoint)) {
      $mobile_breakpoint = '480';
    }
    $mobile_breakpoint = str_replace('px', '', $mobile_breakpoint);

    $cta_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
    $cta_styles .= $col_1_selector . ' { width: 100%; } ';
    $cta_styles .= $col_2_selector . ' { width: 100%; } ';
    $cta_styles .= $cta_selector . ' { flex-direction: column; gap: 0; } ';
    $cta_styles .= ' } ';

    if (!empty($settings['image_position']) && $settings['image_position'] == 'right' &&
      ($settings['styles'] == 'two_columns_fluid' || $settings['styles'] == 'two_columns')) {
      $cta_styles .= $col_1_selector . ' { order: 2; } ';
      $cta_styles .= $col_2_selector . ' { order: 1; } ';
    }

    if (!empty($settings['image_order_mobile']) && $settings['image_order_mobile'] == 'image_first') {
      $cta_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
      $cta_styles .= $col_1_selector . ' { order: 1; } ';
      $cta_styles .= $col_2_selector . ' { order: 2; } ';
      $cta_styles .= ' } ';
    }

    if (!empty($settings['image_order_mobile']) && $settings['image_order_mobile'] == 'image_last') {
      $cta_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
      $cta_styles .= $col_1_selector . ' { order: 2; margin-top: 20px; } ';
      $cta_styles .= $col_2_selector . ' { order: 1; } ';
      $cta_styles .= ' } ';
    }

    if (!empty($settings['styles']) && $settings['styles'] == 'two_columns_fluid') {
      $cta_styles .= '@media screen and (max-width: ' . $mobile_breakpoint . 'px) { ';
      $cta_styles .= '.' . $pagraph_class . '.two_columns_fluid .ept-paragraph-cta__content .column-1 .field--name-field-ept-cta-column-image { ';
      $cta_styles .= ' width: auto; position: inherit; height: auto; ';
      $cta_styles .= ' } ';
      $cta_styles .= ' } ';
    }

    return '<style>' . $cta_styles . '</style>';
  }

}
