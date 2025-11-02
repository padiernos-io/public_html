<?php

namespace Drupal\ept_slick_slider\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_slick_slider' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_slick_slider",
 *   label = @Translation("EPT Slick Slider settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsSlickSliderWidget extends EptSettingsDefaultWidget {

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
      '#title' => $this->t('Slider Style'),
      '#type' => 'radios',
      '#options' => [
        'basic' => $this->t('Basic'),
        'without_styles' => $this->t('Without styles'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'basic',
      '#description' => $this->t('Select additional styles for slick slider'),
    ];

    $element['ept_settings']['autoWidth'] = [
      '#title' => $this->t('Auto width'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['autoWidth'] ?? NULL,
      '#description' => $this->t('If checked, the width of each slide will be its natural width as a inline-block box'),
    ];

    $element['ept_settings']['autoplay'] = [
      '#title' => $this->t('Autoplay'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['autoplay'] ?? NULL,
      '#description' => $this->t('Enables Autoplay'),
      '#attributes' => [
        'class' => ['ept-autoplay-field'],
      ],
    ];

    $element['ept_settings']['autoplaySpeed'] = [
      '#title' => $this->t('Autoplay Speed'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['autoplaySpeed'] ?? '3000',
      '#description' => $this->t('Autoplay Speed in milliseconds'),
       // Set the field as "read-only" when the "autoplay" is unchecked.
      '#states' => [
        'disabled' => [
          ':input.ept-autoplay-field' => ['checked' => FALSE],
        ],
      ],
    ];

    $element['ept_settings']['arrows'] = [
      '#title' => $this->t('Arrows'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['arrows'] ?? 1,
      '#description' => $this->t('Prev/Next Arrows'),
    ];

    $element['ept_settings']['centerMode'] = [
      '#title' => $this->t('Center Mode'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['centerMode'] ?? NULL,
      '#description' => $this->t('Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts'),
    ];

    $element['ept_settings']['centerPadding'] = [
      '#title' => $this->t('Center Padding'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['centerPadding'] ?? '50px',
      '#description' => $this->t('Side padding when in center mode (px or %)'),
    ];

    $element['ept_settings']['dots'] = [
      '#title' => $this->t('Dots'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['dots'] ?? NULL,
      '#description' => $this->t('Show dot indicators'),
    ];

    $element['ept_settings']['infinite'] = [
      '#title' => $this->t('Infinite'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['infinite'] ?? 1,
      '#description' => $this->t('Infinite loop sliding'),
    ];

    $element['ept_settings']['initialSlide'] = [
      '#title' => $this->t('Initial Slide'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['initialSlide'] ?? '0',
      '#description' => $this->t('Slide to start on'),
    ];

    $element['ept_settings']['lazyLoad'] = [
      '#title' => $this->t('Lazy Load'),
      '#type' => 'radios',
      '#options' => [
        'ondemand' => $this->t('On demand'),
        'progressive' => $this->t('Progressive'),
      ],
      '#default_value' => $items[$delta]->ept_settings['lazyLoad'] ?? 'ondemand',
      '#description' => $this->t('Set lazy loading technique'),
    ];

    $element['ept_settings']['mobileFirst'] = [
      '#title' => $this->t('Mobile First'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['mobileFirst'] ?? NULL,
      '#description' => $this->t('Responsive settings use mobile first calculation'),
    ];

    $element['ept_settings']['slidesToShow'] = [
      '#title' => $this->t('Slides To Show'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['slidesToShow'] ?? '1',
      '#description' => $this->t('# of slides to show'),
    ];

    $element['ept_settings']['slidesToScroll'] = [
      '#title' => $this->t('Slides To Scroll'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['slidesToScroll'] ?? '1',
      '#description' => $this->t('# of slides to scroll'),
    ];

    $element['ept_settings']['speed'] = [
      '#title' => $this->t('Speed'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['speed'] ?? '300',
      '#description' => $this->t('Slide/Fade animation speed'),
    ];

    $element['ept_settings']['variableWidth'] = [
      '#title' => $this->t('Variable Width'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['variableWidth'] ?? NULL,
      '#description' => $this->t('Variable width slides'),
    ];

    // Responsive settings.
    $element['ept_settings']['responsive'] = [
      '#type' => 'details',
      '#title' => $this->t('Responsive settings'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['responsive']['mobile'] = [
      '#type' => 'details',
      '#title' => $this->t('Mobile'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['responsive']['mobile']['breakpoint'] = [
      '#title' => $this->t('Breakpoint'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['mobile']['breakpoint'] ?? '576',
      '#description' => $this->t('The breakpoints behave like (min-width: breakpoint) in CSS, so an undefined option will be inherited from previous small breakpoints.'),
    ];

    $element['ept_settings']['responsive']['mobile']['slidesToShow'] = [
      '#title' => $this->t('Slides to show'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['mobile']['slidesToShow'] ?? '',
      '#description' => $this->t('Number of slides being displayed in the viewport.'),
    ];

    $element['ept_settings']['responsive']['mobile']['slidesToScroll'] = [
      '#title' => $this->t('Slides to scroll'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['mobile']['slidesToScroll'] ?? '',
      '#description' => $this->t('Number of slides going on one "click"'),
    ];

    $element['ept_settings']['responsive']['mobile']['centerMode'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['responsive']['mobile']['centerPadding'] ?? '',
      '#description' => $this->t('Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts'),
    ];

    $element['ept_settings']['responsive']['mobile']['centerPadding'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['mobile']['centerPadding'] ?? '',
      '#description' => $this->t('Side padding when in center mode (px or %)'),
    ];

    $element['ept_settings']['responsive']['tablet'] = [
      '#type' => 'details',
      '#title' => $this->t('Tablet'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['responsive']['tablet']['breakpoint'] = [
      '#title' => $this->t('Breakpoint'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['tablet']['breakpoint'] ?? '992',
      '#description' => $this->t('The breakpoints behave like (min-width: breakpoint) in CSS, so an undefined option will be inherited from previous small breakpoints.'),
    ];

    $element['ept_settings']['responsive']['tablet']['slidesToShow'] = [
      '#title' => $this->t('Slides to show'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['tablet']['slidesToShow'] ?? '',
      '#description' => $this->t('Number of slides being displayed in the viewport.'),
    ];

    $element['ept_settings']['responsive']['tablet']['slidesToScroll'] = [
      '#title' => $this->t('Slides to scroll'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['tablet']['slidesToScroll'] ?? '',
      '#description' => $this->t('Number of slides going on one "click"'),
    ];

    $element['ept_settings']['responsive']['tablet']['centerMode'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['responsive']['tablet']['centerPadding'] ?? '',
      '#description' => $this->t('Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts'),
    ];

    $element['ept_settings']['responsive']['tablet']['centerPadding'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['tablet']['centerPadding'] ?? '',
      '#description' => $this->t('Side padding when in center mode (px or %)'),
    ];

    $element['ept_settings']['responsive']['desktop'] = [
      '#type' => 'details',
      '#title' => $this->t('Desktop'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['responsive']['desktop']['breakpoint'] = [
      '#title' => $this->t('Breakpoint'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['desktop']['breakpoint'] ?? '1200',
      '#description' => $this->t('The breakpoints behave like (min-width: breakpoint) in CSS, so an undefined option will be inherited from previous small breakpoints.'),
    ];

    $element['ept_settings']['responsive']['desktop']['slidesToShow'] = [
      '#title' => $this->t('Slides to show'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['desktop']['slidesToShow'] ?? '',
      '#description' => $this->t('Number of slides being displayed in the viewport.'),
    ];

    $element['ept_settings']['responsive']['desktop']['slidesToScroll'] = [
      '#title' => $this->t('Slides to scroll'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['responsive']['desktop']['slidesToScroll'] ?? '',
      '#description' => $this->t('Number of slides going on one "click"'),
    ];

    $element['ept_settings']['responsive']['desktop']['centerMode'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['responsive']['desktop']['centerPadding'] ?? '',
      '#description' => $this->t('Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts'),
    ];

    $element['ept_settings']['responsive']['desktop']['centerPadding'] = [
      '#title' => $this->t('centerPadding'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['responsive']['desktop']['centerPadding'] ?? '',
      '#description' => $this->t('Side padding when in center mode (px or %)'),
    ];

    // Additional settings.
    $element['ept_settings']['additional'] = [
      '#type' => 'details',
      '#title' => $this->t('Additional settings'),
      '#open' => FALSE,
    ];

    $element['ept_settings']['additional']['accessibility'] = [
      '#title' => $this->t('Accessibility'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['accessibility'] ?? 1,
      '#description' => $this->t('Enables tabbing and arrow key navigation'),
    ];

    $element['ept_settings']['additional']['adaptiveHeight'] = [
      '#title' => $this->t('Adaptive Height'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['adaptiveHeight'] ?? NULL,
      '#description' => $this->t('Enables adaptive height for single slide horizontal carousels'),
    ];

    $element['ept_settings']['additional']['draggable'] = [
      '#title' => $this->t('Draggable'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['draggable'] ?? 1,
      '#description' => $this->t('Enable mouse dragging'),
    ];

    $element['ept_settings']['additional']['cssEase'] = [
      '#title' => $this->t('CSS Ease'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['additional']['cssEase'] ?? 'ease',
      '#description' => $this->t('CSS3 Animation Easing'),
    ];

    $element['ept_settings']['additional']['fade'] = [
      '#title' => $this->t('Fade'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['fade'] ?? NULL,
      '#description' => $this->t('Enable fade'),
    ];

    $element['ept_settings']['additional']['focusOnSelect'] = [
      '#title' => $this->t('focus On Select'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['focusOnSelect'] ?? NULL,
      '#description' => $this->t('Enable focus on selected element (click)'),
    ];

    $element['ept_settings']['additional']['easing'] = [
      '#title' => $this->t('Easing'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['additional']['easing'] ?? 'linear',
      '#description' => $this->t('Add easing for jQuery animate. Use with easing libraries or default easing methods'),
    ];

    $element['ept_settings']['additional']['edgeFriction'] = [
      '#title' => $this->t('Edge Friction'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['additional']['edgeFriction'] ?? '0.15',
      '#description' => $this->t('Resistance when swiping edges of non-infinite carousels'),
    ];

    $element['ept_settings']['additional']['pauseOnFocus'] = [
      '#title' => $this->t('Pause On Focus'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['pauseOnFocus'] ?? 1,
      '#description' => $this->t('Pause Autoplay On Focus'),
    ];

    $element['ept_settings']['additional']['pauseOnHover'] = [
      '#title' => $this->t('Pause On Hover'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['pauseOnHover'] ?? 1,
      '#description' => $this->t('Pause Autoplay On Hover'),
    ];

    $element['ept_settings']['additional']['pauseOnDotsHover'] = [
      '#title' => $this->t('Pause On Dots Hover'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['pauseOnDotsHover'] ?? 1,
      '#description' => $this->t('Pause Autoplay when a dot is hovered'),
    ];

    $element['ept_settings']['additional']['respondTo'] = [
      '#title' => $this->t('Respond To'),
      '#type' => 'radios',
      '#options' => [
        'window' => $this->t('window'),
        'slider' => $this->t('slider'),
        'min' => $this->t('min'),
      ],
      '#default_value' => $items[$delta]->ept_settings['additional']['respondTo'] ?? 'window',
      '#description' => $this->t("Width that responsive object responds to. Can be 'window', 'slider' or 'min' (the smaller of the two)"),
    ];

    $element['ept_settings']['additional']['rows'] = [
      '#title' => $this->t('Rows'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['additional']['rows'] ?? '1',
      '#description' => $this->t('Setting this to more than 1 initializes grid mode. Use slidesPerRow to set how many slides should be in each row'),
    ];

    $element['ept_settings']['additional']['slidesPerRow'] = [
      '#title' => $this->t('Slides Per Row'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['additional']['slidesPerRow'] ?? '1',
      '#description' => $this->t('With grid mode intialized via the rows option, this sets how many slides are in each grid row.'),
    ];

    $element['ept_settings']['additional']['swipe'] = [
      '#title' => $this->t('Swipe'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['swipe'] ?? 1,
      '#description' => $this->t('Enable swiping'),
    ];

    $element['ept_settings']['additional']['swipeToSlide'] = [
      '#title' => $this->t('Swipe To Slide'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['swipeToSlide'] ?? NULL,
      '#description' => $this->t('Allow users to drag or swipe directly to a slide irrespective of slidesToScroll'),
    ];

    $element['ept_settings']['additional']['touchMove'] = [
      '#title' => $this->t('Touch Move'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['touchMove'] ?? 1,
      '#description' => $this->t('Enable slide motion with touch'),
    ];

    $element['ept_settings']['additional']['touchThreshold'] = [
      '#title' => $this->t('Touch Threshold'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['additional']['touchThreshold'] ?? '5',
      '#description' => $this->t('To advance slides, the user must swipe a length of (1/touchThreshold) * the width of the slider'),
    ];

    $element['ept_settings']['additional']['useCSS'] = [
      '#title' => $this->t('Use CSS'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['useCSS'] ?? 1,
      '#description' => $this->t('Enable/Disable CSS Transitions'),
    ];

    $element['ept_settings']['additional']['useTransform'] = [
      '#title' => $this->t('Use Transform'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['useTransform'] ?? 1,
      '#description' => $this->t('Enable/Disable CSS Transforms'),
    ];

    $element['ept_settings']['additional']['vertical'] = [
      '#title' => $this->t('Vertical'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['vertical'] ?? NULL,
      '#description' => $this->t('Vertical slide mode'),
    ];

    $element['ept_settings']['additional']['verticalSwiping'] = [
      '#title' => $this->t('verticalSwiping'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['verticalSwiping'] ?? NULL,
      '#description' => $this->t('Vertical swipe mode'),
    ];

    $element['ept_settings']['additional']['rtl'] = [
      '#title' => $this->t('rtl'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['rtl'] ?? NULL,
      '#description' => $this->t("Change the slider's direction to become right-to-left"),
    ];

    $element['ept_settings']['additional']['waitForAnimate'] = [
      '#title' => $this->t('Wait For Animate'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['additional']['waitForAnimate'] ?? 1,
      '#description' => $this->t('Ignores requests to advance the slide while animating'),
    ];

    $element['ept_settings']['additional']['zIndex'] = [
      '#title' => $this->t('zIndex'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->ept_settings['additional']['zIndex'] ?? '1000',
      '#description' => $this->t('Set the zIndex values for slides, useful for IE9 and lower'),
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
