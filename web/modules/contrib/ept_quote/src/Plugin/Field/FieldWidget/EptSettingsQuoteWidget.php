<?php

namespace Drupal\ept_quote\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_quote' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_quote",
 *   label = @Translation("EPT Quote settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsQuoteWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['quote_styles'] = [
      '#type' => 'details',
      '#title' => $this->t('Quote styles'),
      '#open' => TRUE,
    ];

    $element['ept_settings']['quote_styles']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'persona' => $this->t('Persona'),
        'company' => $this->t('Company'),
        'persona_with_small_icon' => $this->t('Persona with small icon'),
        'with_square_image' => $this->t('Width square image'),
        'with_frame_and_background_image' => $this->t('With frame and background image'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'persona',
      '#description' => $this->t('Select predefined styles for quote.'),
    ];

    // Get the path of image helper.
    $image_help_path = '/' . \Drupal::service('extension.path.resolver')->getPath('module', 'ept_quote') . '/images/help/';

    // Define the field help text.
    $description = $this->t('Select predefined styles for quote. You can see some examples below:');
    $description .= '<ul>';
    $description .= '<li>' . $this->t('<a href=":persona_example" target="_blank">Persona</a>', [':persona_example' => $image_help_path . '/persona.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":company_example" target="_blank">Company</a>', [':company_example' => $image_help_path . '/company.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":persona_with_small_icon_example" target="_blank">Persona with small icon</a>', [':persona_with_small_icon_example' => $image_help_path . '/persona-with-small-icon.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":with_square_image_example" target="_blank">Width square image</a>', [':with_square_image_example' => $image_help_path . '/width-square-image.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":with_frame_and_background_image_example" target="_blank">With frame and background image</a>', [':with_frame_and_background_image_example' => $image_help_path . '/with-frame-and-background-image.png']) . '</li>';
    $description .= '<ul>';

    // Set the help text in the field description.
    $element['ept_settings']['quote_styles']['styles']['#description'] = $description;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    // Get the quote styles from group "quote_styles".
    $values[0]['ept_settings']['styles'] = $values[0]['ept_settings']['quote_styles']['styles'];

    foreach ($values as &$value) {
      $value += ['ept_settings' => []];
    }
    return $values;
  }

}
