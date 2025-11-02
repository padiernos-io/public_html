<?php

namespace Drupal\custom_status_report\Element;

use Drupal\system\Element\StatusReportPage;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a custom status report page element.
 *
 * @RenderElement("custom_status_report_page")
 */
class CustomStatusReportPage extends StatusReportPage {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderGeneralInfo($element) {
    $element = parent::preRenderGeneralInfo($element);

    // Get visibility settings.
    $visibility = \Drupal::config('custom_status_report.settings')->get('card_visibility');

    // Filter out hidden sections.
    foreach ($visibility as $section => $visible) {
      if (!$visible) {
        switch ($section) {
          case 'drupal':
            unset($element['#general_info']['#drupal']);
            break;

          case 'webserver':
            unset($element['#general_info']['#webserver']);
            break;

          case 'cron':
            unset($element['#general_info']['#cron']);
            break;

          case 'php':
            unset($element['#general_info']['#php']);
            unset($element['#general_info']['#php_memory_limit']);
            break;

          case 'database':
            unset($element['#general_info']['#database_system']);
            unset($element['#general_info']['#database_system_version']);
            break;
        }
      }
    }

    // Allow modules to add their information to the General Info section.
    foreach ($element['#requirements'] as $key => $requirement) {
      if (isset($requirement['add_to_general_info'])
        && $requirement['add_to_general_info'] === TRUE
        && array_key_exists($key, $visibility)
        && $visibility[$key] == 1) {
        $element['#general_info']['#' . $key] = $requirement;
      }
    }

    // Order the cards by weight from the form.
    $weights = \Drupal::config('custom_status_report.settings')->get('card_weight');
    $ordered_cards = [];
    foreach ($element['#general_info'] as $name => $data) {
      if (is_array($element['#general_info'][$name])) {
        $key = ltrim($name, '#');
        if (array_key_exists($key, $weights)) {
          $ordered_cards[$weights[$key]][$key] = $element['#general_info'][$name];
          // The default Drupal cards need some info that was previously
          // hardcoded into the twig template.
          if ($info = self::getCoreCardInfo($key)) {
            $ordered_cards[$weights[$key]][$key]['title'] = $info['title'];
            $ordered_cards[$weights[$key]][$key]['icon'] = $info['icon'];
          }
          // Some default Drupal cards have extra info.
          if ($key == 'php') {
            $ordered_cards[$weights[$key]][$key]['php_memory_limit'] = $element['#general_info']['#php_memory_limit'];
          }
          elseif ($key == 'database_system') {
            $ordered_cards[$weights[$key]][$key]['database_system_version'] = $element['#general_info']['#database_system_version'];
          }
        }
      }
    }
    ksort($ordered_cards);
    $element['#general_info']['#cards'] = $ordered_cards;
    return $element;
  }

  /**
   * Get the title & icon info for the default cards.
   */
  private static function getCoreCardInfo($module): ?array {
    $core_icons = [
      'drupal' => [
        'title' => t('Drupal Version'),
        'icon' => 'drupal',
      ],
      'webserver' => [
        'title' => t('Web Server'),
        'icon' => 'server',
      ],
      'cron' => [
        'title' => t('Last Cron Run'),
        'icon' => 'clock',
      ],
      'php' => [
        'title' => t('PHP Information'),
        'icon' => 'php',
      ],
      'database_system' => [
        'title' => t('Database Information'),
        'icon' => 'database',
      ],
    ];

    return $core_icons[$module] ?? NULL;
  }

}
