<?php

namespace Drupal\back_to_top_with_pi\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Back to top with progress scrollbar Block.
 *
 * @Block(
 *   id="back_to_top_with_pi",
 *   admin_label = @Translation("Back to top with progress scrollbar"),
 * )
 */
class BackToTopBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'back_to_top_with_pi' => [],
    ];
  }

  /**
   * Overrides \Drupal\Core\Block\BlockBase::blockForm().
   *
   * Adds body and description fields to the block configuration form.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['back_to_top'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Back to top scroll bar configuration'),
      '#description' => $this->t('Configure back to top scroll bar'),
      '#prefix' => '<div id="items-fieldset-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['back_to_top']['circle_stroke_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Circle stroke color'),
      '#default_value' => $config['circle_stroke_color'] ?? '',
      '#attributes' => ['class' => ['major-ticks-width']],
      '#description' => $this->t('Circle stroke color is the line of color that precisely follows a path on page scroll.'),
    ];

    $form['back_to_top']['progress_box_shadow'] = [
      '#type' => 'color',
      '#title' => $this->t('Progress box shadow'),
      '#default_value' => $config['progress_box_shadow'] ?? '',
      '#attributes' => ['class' => ['major-ticks-width']],
      '#description' => $this->t('Progress box shadow color where the stroke will follow a path on page scroll'),
    ];

    $form['back_to_top']['icon_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Progress icon color'),
      '#default_value' => $config['icon_color'] ?? '',
      '#attributes' => ['class' => ['major-ticks-width']],
      '#description' => $this->t('Icon color'),
    ];

    $form['back_to_top']['icon_hover_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Progress icon hover color'),
      '#default_value' => $config['icon_hover_color'] ?? '',
      '#attributes' => ['class' => ['major-ticks-width']],
      '#description' => $this->t('Icon color on hover'),
    ];

    $form['back_to_top']['scroll_bar_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu bar position'),
      '#description' => $this->t('Scrollbar position'),
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => $config['scroll_bar_position'] ?? 'right',
    ];

    $form['back_to_top']['has_shadow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Scrollbar has shadow'),
      '#description' => $this->t('Scrollbar has shadow on hover'),
      '#default_value' => $config['has_shadow'] ?? 'false',
    ];

    $form['back_to_top']['shadow_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Scrollbar shadow color on hover'),
      '#description' => $this->t('Scrollbar shadow color'),
      '#default_value' => $config['shadow_color'] ?? '#212121',
      '#states' => [
        'visible' => [
          ':input[name="settings[back_to_top][has_shadow]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['back_to_top']['has_fill_color'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Scrollbar has fill color'),
      '#description' => $this->t('Scrollbar will be filled with color. The default fill color will be transparent if the checkbox is not checked.'),
      '#default_value' => $config['has_fill_color'] ?? 'false',
    ];

    $form['back_to_top']['fill_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Scrollbar fill color'),
      '#description' => $this->t('Scrollbar fill color'),
      '#default_value' => $config['fill_color'] ?? '#000000',
      '#states' => [
        'visible' => [
          ':input[name="settings[back_to_top][has_fill_color]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['back_to_top']['scrollbar_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Scrollbar type'),
      '#options' => [
        'icon' => $this->t('Scrollbar with icon'),
        'percentage' => $this->t('Scrollbar with percentage'),
      ],
      '#description' => $this->t('Scrollbar display with arrow icon or scroll percentage'),
      '#default_value' => $config['scrollbar_type'] ?? 'icon',
    ];

    $form['back_to_top']['created_at'] = [
      '#type' => 'textfield',
      '#default_value' => $config['created_at'] ?? time(),
      '#access' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if ($values['back_to_top']) {
      $back_to_top = $values['back_to_top'];
      $circle_stroke_color = $back_to_top['circle_stroke_color'];
      $progress_box_shadow = $back_to_top['progress_box_shadow'];
      $icon_color = $back_to_top['icon_color'];
      $icon_hover_color = $back_to_top['icon_hover_color'];
      $scroll_bar_position = $back_to_top['scroll_bar_position'];
      $has_shadow = $back_to_top['has_shadow'];
      $shadow_color = $back_to_top['shadow_color'];
      $has_fill_color = $back_to_top['has_fill_color'];
      $fill_color = $back_to_top['fill_color'];
      $scrollbar_type = $back_to_top['scrollbar_type'];
      $created_at = $back_to_top['created_at'];

      $this->configuration['circle_stroke_color'] = $circle_stroke_color;
      $this->configuration['progress_box_shadow'] = $progress_box_shadow;
      $this->configuration['icon_color'] = $icon_color;
      $this->configuration['icon_hover_color'] = $icon_hover_color;
      $this->configuration['scroll_bar_position'] = $scroll_bar_position;
      $this->configuration['has_shadow'] = $has_shadow;
      $this->configuration['shadow_color'] = $shadow_color;
      $this->configuration['scrollbar_type'] = $scrollbar_type;
      $this->configuration['has_fill_color'] = $has_fill_color;
      $this->configuration['fill_color'] = $fill_color;
      $this->configuration['created_at'] = $created_at;

    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (isset($config['progress_box_shadow']) && $config['progress_box_shadow'] != '') {
      $progress_box_shadow = $config['progress_box_shadow'];
      if (strpos($progress_box_shadow, '#') !== FALSE) {
        [$r, $g, $b] = sscanf($progress_box_shadow, "#%02x%02x%02x");
        $rgb_progress_box_shadow = "inset  0 0 0 2px rgba({$r},{$g},{$b},0.1)";
      }
    }

    if (isset($config['shadow_color']) && $config['shadow_color'] != '') {
      $shadow_color = $config['shadow_color'];
      if (strpos($shadow_color, '#') !== FALSE) {
        [$r, $g, $b] = sscanf($shadow_color, "#%02x%02x%02x");
        $rgb_shadow_color = "rgba({$r},{$g},{$b},0.35) 0px 5px 15px";
      }
    }

    $back_to_top_data = [
      'circle_stroke_color' => ($config['circle_stroke_color']) ?? "inset  0 0 0 2px rgba(0,0,0,0.1)",
      'progress_box_shadow' => ($rgb_progress_box_shadow) ?? '',
      'icon_color' => ($config['icon_color']) ?? '',
      'icon_hover_color' => ($config['icon_hover_color']) ?? '',
      'scroll_bar_position' => ($config['scroll_bar_position']) ?? '',
      'has_shadow' => ($config['has_shadow']) ?? '',
      'shadow_color' => ($rgb_shadow_color) ?? 'rgb(0 0 0 / 35%) 0px 5px 15px',
      'has_fill_color' => ($config['has_fill_color'] && $config['has_fill_color'] == '1') ? TRUE : FALSE,
      'created_at' => ($config['created_at']) ?? time(),
      'scrollbar_type' => ($config['scrollbar_type']) ?? 'icon',
      'fill_color' => ($config['fill_color']) ?? '',
    ];

    // dd($back_to_top_data);
    $build = [];
    $build['back_to_top'] = [
      '#theme' => 'back_to_top_with_pi',
      '#back_to_top_data' => $back_to_top_data,
    ];
    $build['#attributes']['class'][] = 'back-to-top-with-pi-block';

    $build['#attached']['library'][] = 'back_to_top_with_pi/back_to_top_with_pi';
    return $build;
  }

}
