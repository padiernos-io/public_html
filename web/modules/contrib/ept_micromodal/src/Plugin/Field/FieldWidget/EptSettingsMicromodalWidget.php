<?php

namespace Drupal\ept_micromodal\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_micromodal' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_micromodal",
 *   label = @Translation("EPT Micromodal settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsMicromodalWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    $element['ept_settings']['button_text'] = [
      '#title' => $this->t('Button text'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['button_text'] ?? $this->t('Open'),
      '#description' => $this->t('Text for micromodal button.'),
    ];

    $element['ept_settings']['button_type'] = [
      '#title' => $this->t('Button type'),
      '#type' => 'select',
      '#options' => [
        'link' => $this->t('Link'),
        'button' => $this->t('Button'),
      ],
      '#default_value' => $items[$delta]->ept_settings['button_type'] ?? 'link',
      '#description' => $this->t('Select the button type'),
    ];

    $element['ept_settings']['close_button_text'] = [
      '#title' => $this->t('Close Button text'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['close_button_text'] ?? $this->t('Close'),
      '#description' => $this->t('Text for micromodal close button.'),
      '#required' => TRUE,
    ];

    $element['ept_settings']['disable_scroll'] = [
      '#title' => $this->t('Disable scroll'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['disable_scroll'] ?? NULL,
      '#description' => $this->t('This disables scrolling on the page while the modal is open. The default value is false'),
    ];

    $element['ept_settings']['display_close_icon'] = [
      '#title' => $this->t('Display close icon'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['display_close_icon'] ?? TRUE,
      '#description' => $this->t('Keep enabled to display the "X" icon to close the Modal'),
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
