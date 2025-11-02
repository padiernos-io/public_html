<?php

namespace Drupal\ept_stats\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_stats' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_stats",
 *   label = @Translation("EPT Stats settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsStatsWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'stats_with_vertical_dividers' => $this->t('Stats with vertical dividers'),
        'stats_in_squares' => $this->t('Stats in squares'),
        'stats_in_column' => $this->t('Stats in column'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'stats_with_vertical_dividers',
    ];

    // Get the path of image helper.
    $imageHelpPath = '/' . \Drupal::service('extension.path.resolver')->getPath('module', 'ept_stats') . '/images/help/';

    // Define the field help text.
    $description = $this->t('Select predefined styles for stats. You can see some examples below:');
    $description .= '<ul>';
    $description .= '<li>' . $this->t('<a href=":stats_with_vertical_dividers">Stats with vertical dividers</a>', [':stats_with_vertical_dividers' => $imageHelpPath . '/stats_with_vertical_dividers.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":stats_in_squares">Stats in squares</a>', [':stats_in_squares' => $imageHelpPath . '/stats_in_squares.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":stats_in_column">Stats in column</a>', [':stats_in_column' => $imageHelpPath . '/stats_in_column.png']) . '</li>';
    $description .= '<ul>';

    // Set the help text in the field description.
    $element['ept_settings']['styles']['#description'] = $description;

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
