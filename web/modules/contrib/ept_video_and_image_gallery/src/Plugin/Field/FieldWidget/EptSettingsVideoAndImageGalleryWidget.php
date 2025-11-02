<?php

namespace Drupal\ept_video_and_image_gallery\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ept_settings_image_gallery' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_video_and_image_gallery",
 *   label = @Translation("EPT Video and Image Gallery settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsVideoAndImageGalleryWidget extends EptSettingsDefaultWidget {

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
   *   Any third party settings settings.
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

    $element['ept_settings']['image_gallery_styles'] = [
      '#type' => 'html_tag',
      '#tag' => 'h3',
      '#value' => $this->t('Gallery styles:'),
      '#weight' => -21,
    ];

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'one_column' => $this->t('1 Column'),
        'two_columns' => $this->t('2 Columns'),
        'three_columns' => $this->t('3 Columns'),
        'four_columns' => $this->t('4 Columns'),
        'five_columns' => $this->t('5 Columns'),
        'fixed_size_image' => $this->t('Fixed size images grid'),
        'fluid_grid' => $this->t('Fluid grid'),
        'featured_images_grid' => $this->t('Featured images grid'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'four_columns',
      '#description' => $this->t('Select predefined styles for Image Gallery block.'),
      '#weight' => -20,
    ];

    $element['ept_settings']['design_options']['#weight'] = -32;

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
