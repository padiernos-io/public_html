<?php

namespace Drupal\ept_columns\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_columns' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_columns",
 *   label = @Translation("EPT Columns / Container settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsColumnsWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['layout'] = [
      '#title' => $this->t('Choose a layout for this section'),
      '#options' => [
        '1' => 'One column (Container)',
        '2' => 'Two columns',
        '3' => 'Three columns',
        '4' => 'Four columns',
        '5' => 'Five columns',
        '6' => 'Six columns',
      ],
      '#type' => 'radios',
      '#default_value' => $items[$delta]->ept_settings['layout'] ?? 1,
      '#weight' => 5,
    ];

    $element['ept_settings']['column_width_two'] = [
      '#title' => $this->t('Column widths'),
      '#type' => 'select',
      '#options' => [
        '50-50' => '50%/50%',
        '33-67' => '33%/67%',
        '67-33' => '67%/33%',
        '25-75' => '25%/75%',
        '75-25' => '75%/25%',
      ],
      '#default_value' => $items[$delta]->ept_settings['column_width_two'] ?? '50-50',
      '#description' => $this->t('Choose the column widths for this layout.'),
      '#states' => [
        'visible' => [
          ':input[name$="[ept_settings][layout]"]' => [
            'value' => 2,
          ],
        ],
      ],
      '#weight' => 8,
    ];

    $element['ept_settings']['column_width_three'] = [
      '#title' => $this->t('Column widths'),
      '#type' => 'select',
      '#options' => [
        '25-50-25' => '25%/50%/25%',
        '33-34-33' => '33%/34%/33%',
        '25-25-50' => '25%/25%/50%',
        '50-25-25' => '50%/25%/25%',
      ],
      '#default_value' => $items[$delta]->ept_settings['column_width_three'] ?? '33-34-33',
      '#description' => $this->t('Choose the column widths for this layout.'),
      '#states' => [
        'visible' => [
          ':input[name$="[ept_settings][layout]"]' => [
            'value' => 3,
          ],
        ],
      ],
      '#weight' => 8,
    ];

    $element['ept_settings']['column_width_four'] = [
      '#title' => $this->t('Column widths'),
      '#type' => 'select',
      '#options' => [
        '25-25-25-25' => '25%/25%/25%/25%',
        '40-20-20-20' => '40%/20%/20%/20%',
        '20-20-20-40' => '20%/20%/20%/40%',
      ],
      '#default_value' => $items[$delta]->ept_settings['column_width_four'] ?? '25-25-25-25',
      '#description' => $this->t('Choose the column widths for this layout.'),
      '#states' => [
        'visible' => [
          ':input[name$="[ept_settings][layout]"]' => [
            'value' => 4,
          ],
        ],
      ],
      '#weight' => 8,
    ];

    $element['ept_settings']['equal_height'] = [
      '#title' => $this->t('Equal height'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['equal_height'] ?? '1',
      '#description' => $this->t('Equal height for all columns.'),
      '#weight' => 15,
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
