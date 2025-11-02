<?php

namespace Drupal\ept_timeline\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_timeline' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_timeline",
 *   label = @Translation("EPT Timeline settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsTimelineWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'simple_vertical' => $this->t('Simple vertical'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'simple_vertical',
      '#description' => $this->t('Select predefined styles for timeline.'),
      '#disabled' => TRUE,
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
