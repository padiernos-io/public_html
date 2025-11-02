<?php

namespace Drupal\ept_hero\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_basic_button\Plugin\Field\FieldWidget\EptSettingsBasicButtonWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ept_settings_hero' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_hero",
 *   label = @Translation("EPT Hero settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsHeroWidget extends EptSettingsBasicButtonWidget {

  /**
   * The EPT Core configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs a new GenerateCSS object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ConfigFactoryInterface $config_factory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->config = $config_factory->get('ept_core.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => FALSE,
    ];

    $element['ept_settings']['button_styles'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t('Button styles:'),
      '#weight' => -1,
    ];

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'two_columns' => $this->t('2 Columns'),
        'one_column' => $this->t('One column'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'two_columns',
      '#description' => $this->t('Select predefined styles for Hero block.'),
      '#weight' => -20,
    ];

    $element['ept_settings']['overlay'] = [
      '#title' => $this->t('Add overlay'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['overlay'] ?? 0,
      '#description' => $this->t('Add overlay between background image and text.'),
      '#weight' => -20,
    ];

    $element['ept_settings']['overlay_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overlay Color'),
      '#default_value' => $items[$delta]->ept_settings['overlay_color'] ?? '#000000',
      '#attributes' => [
        'placeholder' => $this->t('Select RGB Color, for example #000000'),
      ],
      '#weight' => -20,
    ];

    $element['ept_settings']['overlay_alpha'] = [
      '#type' => 'number',
      '#min' => 0,
      '#max' => 1,
      '#step' => 0.01,
      '#title' => $this->t('Overlay opacity'),
      '#default_value' => $items[$delta]->ept_settings['overlay_alpha'] ?? '0.6',
      '#description' => $this->t('Opacity for overlay.'),
      '#weight' => -20,
    ];

    $element['ept_settings']['image_position'] = [
      '#title' => $this->t('Image position'),
      '#type' => 'radios',
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $items[$delta]->ept_settings['image_position'] ?? 'left',
      '#description' => $this->t('Image position in 2 columns layout.'),
      '#weight' => -20,
    ];

    $element['ept_settings']['image_order_mobile'] = [
      '#title' => $this->t('Image position on mobile'),
      '#type' => 'radios',
      '#options' => [
        'image_first' => $this->t('Image first'),
        'image_last' => $this->t('Image last'),
        'hide_image' => $this->t('Hide image'),
      ],
      '#default_value' => $items[$delta]->ept_settings['image_order_mobile'] ?? 'image_first',
      '#description' => $this->t('Image position in mobile version after transition from 2 to 1 columns.'),
      '#weight' => -19,
    ];

    $mobile_breakpoint_default = $this->config->get('ept_core_mobile_breakpoint');
    if (empty($mobile_breakpoint_default)) {
      $mobile_breakpoint_default = 480;
    }
    $element['ept_settings']['mobile_breakpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mobile breakpoint'),
      '#default_value' => !empty($items[$delta]->ept_settings['mobile_breakpoint']) ? $items[$delta]->ept_settings['mobile_breakpoint'] : $mobile_breakpoint_default,
      '#attributes' => [
        'placeholder' => $this->t('Enter breakpoint'),
      ],
      '#description' => $this->t('Mobile breakpoint in pixels to switch 2 columns in one column'),
      '#weight' => -18,
    ];

    $element['ept_settings']['design_options']['#weight'] = -32;

    $element['ept_settings']['link_options2'] = $element['ept_settings']['link_options'];
    $element['ept_settings']['link_options2']['#weight'] = 3;
    $element['ept_settings']['link_options2']['#title'] = $this->t('Second Link options');

    $element['ept_settings']['link_options2']['open_in_new_tab']['#default_value'] = $items[$delta]->ept_settings['link_options2']['open_in_new_tab'] ?? NULL;
    $element['ept_settings']['link_options2']['add_nofollow']['#default_value'] = $items[$delta]->ept_settings['link_options2']['add_nofollow'] ?? NULL;
    $element['ept_settings']['link_options2']['title_color']['#default_value'] = $items[$delta]->ept_settings['link_options2']['title_color'] ?? NULL;
    $element['ept_settings']['link_options2']['background_color']['#default_value'] = $items[$delta]->ept_settings['link_options2']['background_color'] ?? NULL;
    $element['ept_settings']['link_options2']['custom_hover_colors']['#default_value'] = $items[$delta]->ept_settings['link_options2']['custom_hover_colors'] ?? NULL;
    $element['ept_settings']['link_options2']['hover_title_color']['#default_value'] = $items[$delta]->ept_settings['link_options2']['hover_title_color'] ?? NULL;
    $element['ept_settings']['link_options2']['hover_background_color']['#default_value'] = $items[$delta]->ept_settings['link_options2']['hover_background_color'] ?? NULL;
    $element['ept_settings']['link_options2']['alignment']['#default_value'] = $items[$delta]->ept_settings['link_options2']['alignment'] ?? NULL;
    $element['ept_settings']['link_options2']['shape']['#default_value'] = $items[$delta]->ept_settings['link_options2']['shape'] ?? NULL;
    $element['ept_settings']['link_options2']['size']['#default_value'] = $items[$delta]->ept_settings['link_options2']['size'] ?? NULL;
    $element['ept_settings']['link_options2']['stretched']['#default_value'] = $items[$delta]->ept_settings['link_options2']['stretched'] ?? NULL;
    $element['ebt_settings']['link_options2']['custom_class_name']['#default_value'] = $items[$delta]->ebt_settings['link_options2']['custom_class_name'] ?? NULL;


    $element['ept_settings']['elements_classes'] = [
      '#type' => 'details',
      '#title' => $this->t('Elements additional classes'),
      '#weight' => 4,
    ];

    $element['ept_settings']['elements_classes']['bg_inner_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BG Inner classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['bg_inner_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['ept_container_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EPT container classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['ept_container_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['ept_hero_container_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('EPT Hero container classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['ept_hero_container_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['hero_col_1_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column 1 classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['hero_col_1_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['hero_col_2_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column 2 classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['hero_col_2_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['buttons_wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Buttons wrapper classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['buttons_wrapper_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['button_1_wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 1 wrapper classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['button_1_wrapper_classes'] ?? '',
      '#element_validate' => [
        [
          '\Drupal\ept_core\Helper\EptGenericValidator',
          'validateClassElement',
        ],
      ],
    ];

    $element['ept_settings']['elements_classes']['button_2_wrapper_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Button 2 wrapper classes'),
      '#default_value' => $items[$delta]->ept_settings['elements_classes']['button_2_wrapper_classes'] ?? '',
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
