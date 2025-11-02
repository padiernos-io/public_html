<?php

namespace Drupal\ept_slideshow\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_slideshow' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_slideshow",
 *   label = @Translation("EPT Slideshow settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsSlideshowWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    $element['ept_settings']['animation'] = [
      '#title' => $this->t('Animation'),
      '#type' => 'radios',
      '#options' => [
        'fade' => $this->t('Fade'),
        'slide' => $this->t('Slide'),
      ],
      '#default_value' => $items[$delta]->ept_settings['animation'] ?? 'fade',
      '#description' => $this->t('Select your animation type. Carousel displaying require "Slide" option is selected.'),
    ];

    $element['ept_settings']['direction'] = [
      '#title' => $this->t('Direction'),
      '#type' => 'radios',
      '#options' => [
        'horizontal' => $this->t('Horizontal'),
        'vertical' => $this->t('Vertical'),
      ],
      '#default_value' => $items[$delta]->ept_settings['direction'] ?? 'horizontal',
      '#description' => $this->t('Select your animation type.'),
    ];

    $element['ept_settings']['reverse'] = [
      '#title' => $this->t('Reverse'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['reverse'] ?? NULL,
      '#description' => $this->t('Reverse the animation direction.'),
    ];

    $element['ept_settings']['animationLoop'] = [
      '#title' => $this->t('Animation loop'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['animationLoop'] ?? 1,
      '#description' => $this->t('Should the animation loop? If false, directionNav will received "disable" classes at either end.'),
    ];

    $element['ept_settings']['smoothHeight'] = [
      '#title' => $this->t('Smooth height'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['smoothHeight'] ?? NULL,
      '#description' => $this->t('Allow height of the slider to animate smoothly in horizontal mode.'),
    ];

    $element['ept_settings']['startAt'] = [
      '#title' => $this->t('Start at'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['startAt'] ?? 0,
      '#description' => $this->t('Integer: The slide that the slider should start on. Array notation (0 = first slide)'),
    ];

    $element['ept_settings']['slideshow'] = [
      '#title' => $this->t('Slideshow'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['slideshow'] ?? NULL,
      '#description' => $this->t('Animate slider automatically.'),
    ];

    $element['ept_settings']['animationSpeed'] = [
      '#title' => $this->t('Animation speed'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['animationSpeed'] ?? 600,
      '#description' => $this->t('Integer: Set the speed of animations, in milliseconds'),
    ];

    $element['ept_settings']['slideshowSpeed'] = [
      '#title' => $this->t('Slideshow speed'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['slideshowSpeed'] ?? 7000,
      '#description' => $this->t('Integer: Set the speed of the slideshow cycling, in milliseconds'),
    ];

    $element['ept_settings']['initDelay'] = [
      '#title' => $this->t('Init delay'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['initDelay'] ?? 0,
      '#description' => $this->t('Integer: Set an initialization delay, in milliseconds'),
    ];

    $element['ept_settings']['randomize'] = [
      '#title' => $this->t('Randomize'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['randomize'] ?? NULL,
      '#description' => $this->t('Randomize slide order.'),
    ];

    $element['ept_settings']['fadeFirstSlide'] = [
      '#title' => $this->t('Fade first slide'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['fadeFirstSlide'] ?? 1,
      '#description' => $this->t('Fade in the first slide when animation type is "fade".'),
    ];

    $element['ept_settings']['thumbCaptions'] = [
      '#title' => $this->t('Thumb captions'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['thumbCaptions'] ?? NULL,
      '#description' => $this->t('Whether or not to put captions on thumbnails when using the "thumbnails" controlNav.'),
    ];

    $element['ept_settings']['usability'] = [
      '#type' => 'details',
      '#title' => $this->t('Navigation settings'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['usability']['pauseOnHover'] = [
      '#title' => $this->t('Pause on hover'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['usability']['pauseOnHover'] ?? NULL,
      '#description' => $this->t('Pause the slideshow when hovering over slider, then resume when no longer hovering.'),
    ];

    $element['ept_settings']['usability']['controlNav'] = [
      '#title' => $this->t('Control navigation'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['usability']['controlNav'] ?? 1,
      '#description' => $this->t('Create navigation for paging control of each slide? Note: Leave true for manualControls usage.'),
    ];

    $element['ept_settings']['usability']['directionNav'] = [
      '#title' => $this->t('Direction navigation'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['usability']['directionNav'] ?? 1,
      '#description' => $this->t('Create navigation for previous/next navigation?.'),
    ];

    $element['ept_settings']['usability']['prevText'] = [
      '#title' => $this->t('Previous text'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['usability']['prevText'] ?? 'Previous',
      '#description' => $this->t('Set the text for the "previous" directionNav item'),
    ];

    $element['ept_settings']['usability']['nextText'] = [
      '#title' => $this->t('Next text'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['usability']['nextText'] ?? 'Next',
      '#description' => $this->t('Set the text for the "next" directionNav item'),
    ];

    $element['ept_settings']['usability']['pausePlay'] = [
      '#title' => $this->t('Pause play'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['usability']['pausePlay'] ?? NULL,
      '#description' => $this->t('Create pause/play dynamic element'),
    ];

    $element['ept_settings']['usability']['pauseText'] = [
      '#title' => $this->t('Pause'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['usability']['pauseText'] ?? 'Pause',
      '#description' => $this->t('Set the text for the "pause" pausePlay item'),
    ];

    $element['ept_settings']['usability']['playText'] = [
      '#title' => $this->t('Play text'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['usability']['playText'] ?? 'Play',
      '#description' => $this->t('Set the text for the "play" pausePlay item'),
    ];

    $element['ept_settings']['carousel'] = [
      '#type' => 'details',
      '#title' => $this->t('Carousel settings'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['carousel']['itemWidth'] = [
      '#title' => $this->t('Item width'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['carousel']['itemWidth'] ?? 0,
      '#description' => $this->t('Integer: Box-model width of individual carousel items, including horizontal borders and padding.'),
    ];

    $element['ept_settings']['carousel']['itemMargin'] = [
      '#title' => $this->t('Item margin'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['carousel']['itemMargin'] ?? 0,
      '#description' => $this->t('Integer: Margin between carousel items.'),
    ];

    $element['ept_settings']['carousel']['minItems'] = [
      '#title' => $this->t('Min items'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['carousel']['minItems'] ?? 1,
      '#description' => $this->t('Integer: Minimum number of carousel items that should be visible. Items will resize fluidly when below this.'),
    ];

    $element['ept_settings']['carousel']['maxItems'] = [
      '#title' => $this->t('Max items'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['carousel']['maxItems'] ?? 0,
      '#description' => $this->t('Integer: Maxmimum number of carousel items that should be visible. Items will resize fluidly when above this limit.'),
    ];

    $element['ept_settings']['carousel']['move'] = [
      '#title' => $this->t('Move'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['carousel']['move'] ?? 0,
      '#description' => $this->t('Integer: Number of carousel items that should move on animation. If 0, slider will move all visible items.'),
    ];

    $element['ept_settings']['carousel']['allowOneSlide'] = [
      '#title' => $this->t('Allow one slide'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['carousel']['allowOneSlide'] ?? 1,
      '#description' => $this->t('Whether or not to allow a slider comprised of a single slide'),
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
