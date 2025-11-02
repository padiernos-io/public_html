<?php

namespace Drupal\ept_basic_button\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_basic_button' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_basic_button",
 *   label = @Translation("EPT Basic Button settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsBasicButtonWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Get the config object of ept_core.
    $config = \Drupal::config('ept_core.settings');

    $element['#attached']['library'][] = 'ept_core/colorpicker';
    $element['#attached']['library'][] = 'ept_basic_button/ept_basic_button_form';

    $element['ept_settings']['link_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Link options'),
      '#weight' => 0,
    ];

    $element['ept_settings']['link_options']['open_in_new_tab'] = [
      '#title' => $this->t('Open the link in a new tab'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['open_in_new_tab'] ?? NULL,
    ];

    $element['ept_settings']['link_options']['add_nofollow'] = [
      '#title' => $this->t('Add "nofollow" option to the link'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['add_nofollow'] ?? NULL,
      '#description' => $this->t('The nofollow attribute is an HTML attribute in the link tag to tell search engines not to follow the link when crawling the web page'),
    ];

    $element['ept_settings']['link_options']['title_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title Color'),
      '#default_value' => $items[$delta]->ept_settings['title_color'] ?? '#ffffff',
      '#attributes' => [
        'placeholder' => $this->t('Title Color'),
      ],
      '#element_validate' => [
        [
          '\Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget',
          'validateColorElement',
        ],
      ],
    ];

    $element['ept_settings']['link_options']['background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Background Color'),
      // If "background_color" is empty, use the default value from settings.
      '#default_value' => $items[$delta]->ept_settings['background_color'] ?? $config->get('ept_core_background_color'),
      '#attributes' => [
        'placeholder' => $this->t('Background Color'),
      ],
      '#element_validate' => [
        [
          '\Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget',
          'validateColorElement',
        ],
      ],
    ];

    $element['ept_settings']['link_options']['custom_hover_colors'] = [
      '#title' => $this->t('Custom hover colors'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['custom_hover_colors'] ?? NULL,
      '#attributes' => [
        'class' => ['ept-custom-hover-colors-field'],
      ],
      '#description' => $this->t('If checked, you will be able to choose a color for both the "Hover Title" and "Hover Background"'),
    ];

    $element['ept_settings']['link_options']['hover_title_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hover Title Color'),
      '#default_value' => $items[$delta]->ept_settings['hover_title_color'] ?? '',
      '#attributes' => [
        'placeholder' => $this->t('Hover Title Color'),
      ],
      // If the option "custom_hover_colors" is "unchecked" so hide the fields
      // related to hover colors.
      '#states' => [
        'invisible' => [
          ':input.ept-custom-hover-colors-field' => ['checked' => FALSE],
        ],
      ],
      '#element_validate' => [
        [
          '\Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget',
          'validateColorElement',
        ],
      ],
    ];

    $element['ept_settings']['link_options']['hover_background_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hover Background Color'),
      '#default_value' => $items[$delta]->ept_settings['hover_background_color'] ?? '',
      '#attributes' => [
        'placeholder' => $this->t('Hover Background Color'),
      ],
      // If the option "custom_hover_colors" is "unchecked" so hide the fields
      // related to hover colors.
      '#states' => [
        'invisible' => [
          ':input.ept-custom-hover-colors-field' => ['checked' => FALSE],
        ],
      ],
      '#element_validate' => [
        [
          '\Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget',
          'validateColorElement',
        ],
      ],
    ];

    $element['ept_settings']['link_options']['alignment'] = [
      '#title' => $this->t('Alignment'),
      '#type' => 'radios',
      '#options' => [
        'left' => $this->t('Left'),
        'center' => $this->t('Center'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $items[$delta]->ept_settings['alignment'] ?? 'left',
    ];

    // Get the path of image helper.
    $imageHelpPath = '/' . \Drupal::service('extension.path.resolver')->getPath('module', 'ept_basic_button') . '/images/help/';

    $element['ept_settings']['link_options']['shape'] = [
      '#title' => $this->t('Shape'),
      '#type' => 'radios',
      '#options' => [
        'square' => $this->t('Square'),
        'round' => $this->t('Round'),
        'circle' => $this->t('Circle'),
      ],
      '#default_value' => $items[$delta]->ept_settings['shape'] ?? 'square',
      '#description' => $this->t('<a href=":shape_examples">Click here</a> to view some examples of shapes', [
        ':shape_examples' => $imageHelpPath . '/shape-examples.png',
      ]),
    ];

    $element['ept_settings']['link_options']['size'] = [
      '#title' => $this->t('Size'),
      '#type' => 'radios',
      '#options' => [
        'small' => $this->t('Small'),
        'medium' => $this->t('Medium'),
        'large' => $this->t('Large'),
      ],
      '#default_value' => $items[$delta]->ept_settings['size'] ?? 'medium',
    ];

    // @todo Remove in 2.x version.
    if (!empty($items[$delta]->ept_settings['stretched'])) {
      $value = $items[$delta]->ept_settings['stretched'];
    }
    elseif (!empty($items[$delta]->ept_settings['stetched'])) {
      $value = $items[$delta]->ept_settings['stetched'];
    }
    else {
      $value = NULL;
    }

    $element['ept_settings']['link_options']['stretched'] = [
      '#title' => $this->t('Stretched'),
      '#type' => 'checkbox',
      '#default_value' => $value,
      '#description' => $this->t('Check if you want to stretch the width of the button'),
    ];

    $element['ept_settings']['link_options']['custom_class_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom class name'),
      '#default_value' => $items[$delta]->ept_settings['custom_class_name'] ?? '',
      '#description' => $this->t('Customize the styling of this paragraph by adding CSS classes. Separate multiple classes by spaces'),
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
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
    foreach ($values[0]['ept_settings']['link_options'] as $key => $option) {
      $values[0]['ept_settings'][$key] = $option;
    }
    return $values;
  }

}
