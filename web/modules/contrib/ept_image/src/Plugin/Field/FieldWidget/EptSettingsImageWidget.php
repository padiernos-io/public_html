<?php

namespace Drupal\ept_image\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;
use Drupal\image\Entity\ImageStyle;

/**
 * Plugin implementation of the 'ept_settings_image' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_image",
 *   label = @Translation("EPT Image settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsImageWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $styles = ImageStyle::loadMultiple();
    $image_styles['none'] = $this->t('Original image');
    foreach ($styles as $key => $style) {
      $image_styles[$key] = $style->label();
    }

      $element['ept_settings']['image_style'] = [
      '#title' => $this->t('Image Style'),
      '#type' => 'select',
      '#options' => $image_styles,
      '#default_value' => $items[$delta]->ept_settings['image_style'] ?? 'none',
      '#description' => $this->t('Select image style for image.'),
      '#weight' => 4,
    ];

    $element['ept_settings']['image_lightbox'] = [
      '#title' => $this->t('Enable Image Lightbox'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['image_lightbox'] ?? FALSE,
      '#description' => $this->t('Display lightbox on image click.'),
      '#weight' => 5,
    ];

    $element['ept_settings']['lightbox_image_style'] = [
      '#title' => $this->t('Lightbox Image Style'),
      '#type' => 'select',
      '#options' => $image_styles,
      '#default_value' => $items[$delta]->ept_settings['lightbox_image_style'] ?? 'none',
      '#description' => $this->t('Select image style for lightbox image.'),
      '#weight' => 6,
    ];

    $element['ept_settings']['greyscale'] = [
      '#title' => $this->t('Greyscale'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['greyscale'] ?? FALSE,
      '#description' => $this->t('Greyscale image.'),
      '#weight' => 7,
    ];

    $element['ept_settings']['colorful_on_hover'] = [
      '#title' => $this->t('Colorful on hover'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['colorful_on_hover'] ?? FALSE,
      '#description' => $this->t('Make images colorful on hover when greyscale enabled.'),
      '#weight' => 8,
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
