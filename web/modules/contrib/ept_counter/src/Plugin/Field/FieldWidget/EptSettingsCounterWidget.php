<?php

namespace Drupal\ept_counter\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_counter' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_counter",
 *   label = @Translation("EPT Counter settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsCounterWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    $element['ept_settings']['design_options']['#weight'] = -32;
    $element['ept_settings']['counter_styles'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t('Counter styles:'),
      '#weight' => -1,
    ];

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'two_columns' => $this->t('2 column'),
        'three_columns' => $this->t('3 Columns'),
        'four_columns' => $this->t('4 Columns'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'four_columns',
      '#description' => $this->t('Select predefined styles for Counter block.'),
      '#weight' => -20,
    ];

    // Options Counter javascript plugin.
    // @see https://github.com/inorganik/CountUp.js
    $element['ept_settings']['startVal'] = [
      '#title' => $this->t('Start Value'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['startVal'] ?? 0,
      '#description' => $this->t('number to start at'),
    ];

    $element['ept_settings']['prefix'] = [
      '#title' => $this->t('Prefix'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['prefix'] ?? '',
      '#description' => $this->t('Text prepended to result'),
    ];

    $element['ept_settings']['suffix'] = [
      '#title' => $this->t('Suffix'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['suffix'] ?? '',
      '#description' => $this->t('Text appended to result'),
    ];

    $element['ept_settings']['decimalPlaces'] = [
      '#title' => $this->t('Decimal Places'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['decimalPlaces'] ?? 0,
      '#description' => $this->t('Number of decimal places'),
    ];

    $element['ept_settings']['duration'] = [
      '#title' => $this->t('Duration'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['duration'] ?? 2,
      '#description' => $this->t('Animation duration in seconds'),
    ];

    $element['ept_settings']['useGrouping'] = [
      '#title' => $this->t('Use Grouping'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['useGrouping'] ?? 1,
      '#description' => $this->t('Example: 1,000 vs 1000'),
    ];

    $element['ept_settings']['separator'] = [
      '#title' => $this->t('Separator'),
      '#type' => 'radios',
      '#options' => [
        'comma' => $this->t('Comma 1,000'),
        'dot' => $this->t('Dot 1.000'),
        'dash' => $this->t('Dash 1-000'),
      ],
      '#default_value' => $items[$delta]->ept_settings['separator'] ?? 'comma',
      '#description' => $this->t('Grouping separator, for example: 1,000 vs 1000'),
    ];

    $element['ept_settings']['useEasing'] = [
      '#title' => $this->t('Use Easing'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['useEasing'] ?? 1,
      '#description' => $this->t('Ease animation'),
    ];

    $element['ept_settings']['smartEasingThreshold'] = [
      '#title' => $this->t('Smart Easing Threshold'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['smartEasingThreshold'] ?? 999,
      '#description' => $this->t('Smooth easing for large numbers above this if useEasing'),
    ];

    $element['ept_settings']['smartEasingAmount'] = [
      '#title' => $this->t('Smart Easing Amount'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['smartEasingAmount'] ?? 333,
      '#description' => $this->t('Amount to be eased for numbers above threshold'),
    ];

    $element['ept_settings']['enableScrollSpy'] = [
      '#title' => $this->t('Enable Scroll Spy'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['enableScrollSpy'] ?? 1,
      '#description' => $this->t('Start animation when target is in view'),
    ];

    $element['ept_settings']['scrollSpyDelay'] = [
      '#title' => $this->t('Scroll Spy Delay'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['scrollSpyDelay'] ?? 0,
      '#description' => $this->t('delay (ms) after target comes into view'),
    ];

    $element['ept_settings']['scrollSpyOnce'] = [
      '#title' => $this->t('Scroll Spy Once'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['scrollSpyOnce'] ?? 0,
      '#description' => $this->t('Run only once'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value += ['ept_settings' => []];
    }
    return $values;
  }

}
