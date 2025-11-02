<?php

namespace Drupal\ept_basic_button\Services;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ept_core\Constants\EptConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Transform Block settings in CSS.
 */
class GenerateCustomCSS implements ContainerInjectionInterface {

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
    $styles = '';
    $hover_styles = '';
    $button_selector = '.' . $paragraph_class . ' .ept-basic-button';

    if (!empty($settings['title_color'])) {
      $title_color = str_replace('#', '', $settings['title_color']);
      $title_color = '#' . Html::escape($title_color);
    }
    else {
      $title_color = EptConstants::COLOR_WHITE;
    }

    $styles .= ' color: ' . $title_color . ';';

    if (!empty($settings['background_color'])) {
      $background_color = str_replace('#', '', $settings['background_color']);
      $background_color = '#' . Html::escape($background_color);
    }
    else {
      // If "background_color" is empty, use the default value from settings.
      $background_color = $this->config->get('ept_core_background_color');
    }

    $styles .= ' background-color: ' . $background_color . ';';

    if (!empty($settings['custom_hover_colors'])) {
      if (!empty($settings['hover_title_color'])) {
        $title_color = str_replace('#', '', $settings['hover_title_color']);
        $title_color = '#' . Html::escape($title_color);
      }
      else {
        $title_color = '#fff';
      }
      $hover_styles .= ' color: ' . $title_color . ';';

      if (!empty($settings['hover_background_color'])) {
        $background_color = str_replace('#', '', $settings['hover_background_color']);
        $background_color = '#' . Html::escape($background_color);
      }
      else {
        // Add primary bg color from global settings form.
        $background_color = $this->config->get('ept_core_background_color');
      }
      $hover_styles .= ' background-color: ' . $background_color . ';';
    }

    $button_styles2 = '';
    $hover_styles2 = '';
    $button_selector2 = '.' . $paragraph_class . ' .ept-basic-button2';

    if (!empty($settings['link_options2']['title_color'])) {
      $title_color = str_replace('#', '', $settings['link_options2']['title_color']);
      $title_color = '#' . Html::escape($title_color);
      $button_styles2 .= ' color: ' . $title_color . ';';
    }

    if (!empty($settings['link_options2']['background_color'])) {
      $background_color = str_replace('#', '', $settings['link_options2']['background_color']);
      $background_color = '#' . Html::escape($background_color);
      $button_styles2 .= ' background-color: ' . $background_color . ';';
    }

    if (!empty($settings['link_options2']['custom_hover_colors'])) {
      if (!empty($settings['link_options2']['hover_title_color'])) {
        $title_color = str_replace('#', '', $settings['link_options2']['hover_title_color']);
        $title_color = '#' . Html::escape($title_color);
        $hover_styles2 .= ' color: ' . $title_color . ';';
      }

      if (!empty($settings['link_options2']['hover_background_color'])) {
        $background_color = str_replace('#', '', $settings['link_options2']['hover_background_color']);
        $background_color = '#' . Html::escape($background_color);
        $hover_styles2 .= ' background-color: ' . $background_color . ';';
      }
    }

    return '<style>' .
      $button_selector . '{' . $styles . '} ' . $button_selector . ':hover {' . $hover_styles . '} ' .
      $button_selector2 . '{' . $button_styles2 . '} ' . $button_selector2 . ':hover {' . $hover_styles2 . '} ' .
      ' </style>';
  }

}
