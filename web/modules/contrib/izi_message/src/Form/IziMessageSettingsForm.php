<?php

namespace Drupal\izi_message\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures izi message settings.
 */
class IziMessageSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'izi_message.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'izi_message_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['izi_message.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config(static::SETTINGS);

    $form['izi_message_size'] = [
      '#type' => 'horizontal_tabs',
      '#tree' => TRUE,
      '#prefix' => '<div id="izi_message_tabs">',
      '#suffix' => '</div>',
    ];

    $form['izi_message_basic'] = [
      '#type' => 'details',
      '#title' => $this->t('Basic settings'),
      '#open' => TRUE,
      '#weight' => -100,
      'timeout' => [
        '#type'  => 'number',
        '#title'  => $this->t('Timeout'),
        '#description'  => $this->t('Amount in milliseconds to close the toast.'),
        '#default_value'  => $config->get('timeout'),
        '#step' => 10,
        '#min' => 0,
        '#max' => 99999,
      ],
      'position' => [
        '#type'     => 'select',
        '#title'    => $this->t('Message Position'),
        '#description'    => $this->t('Where it will be shown.'),
        '#default_value'  => $config->get('position'),
        '#options'  => $this->getPotitionOptions(),
      ],
      'drag' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Drag Feature'),
        '#description'    => $this->t('Is used to close the toast.'),
        '#default_value' => $config->get('drag'),
      ],
      'progressBar' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Progress Bar'),
        '#description'    => $this->t('Enable timeout progress bar.'),
        '#default_value' => $config->get('progressBar'),
      ],
      'pauseOnHover' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Pause on hover'),
        '#description'    => $this->t('Pause the toast timeout while the cursor is on it.'),
        '#default_value' => $config->get('pauseOnHover'),
      ],
    ];

    $form['izi_message_size'] = [
      '#type' => 'details',
      '#title' => $this->t('Setting size'),
      'titleSize' => [
        '#type'  => 'number',
        '#title' => $this->t('Title font size.'),
        '#default_value'  => $config->get('titleSize'),
        '#placeholder' => $this->t('For example: 16 or 24'),
        '#step' => 1,
        '#min' => 0,
        '#max' => 180,
      ],
      'messageSize' => [
        '#type'  => 'number',
        '#title' => $this->t('Message font size.'),
        '#default_value'  => $config->get('messageSize'),
        '#step' => 1,
        '#min' => 0,
        '#max' => 255,
      ],
      'maxWidth' => [
        '#type'           => 'number',
        '#title'          => $this->t('Max width'),
        '#description'    => $this->t('Max width of messages. Example 500 - it will be 500px'),
        '#default_value'  => $config->get('maxWidth'),
      ],
    ];

    $form['izi_message_animation'] = [
      '#type' => 'details',
      '#title' => $this->t('Setting animations'),

      'animateInside' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Animate inside'),
        '#description'    => $this->t('Enable animations of elements in the toast.'),
        '#default_value' => $config->get('animateInside'),
      ],
      'transitionIn' => [
        '#type'     => 'select',
        '#title'    => $this->t('Transition in'),
        '#description'    => $this->t('Default toast open animation'),
        '#default_value'  => $config->get('transitionIn'),
        '#options'  => $this->getInAnimationOptions(),
      ],
      'transitionOut' => [
        '#type'     => 'select',
        '#title'    => $this->t('Transition out'),
        '#description' => $this->t('Default toast close animation.'),
        '#default_value'  => $config->get('transitionOut'),
        '#options'  => $this->getOutAnimationOptions(),
      ],
      'transitionInMobile' => [
        '#type'     => 'select',
        '#title'    => $this->t('Mobile transition in'),
        '#description'    => $this->t('Default toast opening mobile transition.'),
        '#default_value'  => $config->get('transitionInMobile'),
        '#options'  => $this->getInAnimationOptions(),
      ],
      'transitionOutMobile' => [
        '#type'     => 'select',
        '#title'    => $this->t('Mobile transition out'),
        '#description'    => $this->t('Default toast closing mobile transition.'),
        '#default_value'  => $config->get('transitionOutMobile'),
        '#options'  => $this->getOutAnimationOptions(),
      ],
    ];

    $form['izi_message_close'] = [
      '#type' => 'details',
      '#title' => $this->t('Close settings'),
      'close' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Close button'),
        '#description'    => $this->t('Show "x" close button'),
        '#default_value' => $config->get('close'),
      ],
      'closeOnEscape' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Close on escape'),
        '#description'    => $this->t('Allows to close toast using the Esc key.'),
        '#default_value' => $config->get('closeOnEscape'),
      ],
      'closeOnClick' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Close on click'),
        '#description'    => $this->t('Allows to close toast clicking on it.'),
        '#default_value' => $config->get('closeOnClick'),
      ],
    ];

    $form['izi_message_other'] = [
      '#type' => 'details',
      '#title' => $this->t('Other settings'),
      'displayMode' => [
        '#type'           => 'number',
        '#title'          => $this->t('Display mode'),
        '#description'    => $this->t('Use 1 or "once", use 2 or "replace"'),
        '#default_value'  => $config->get('displayMode'),
        '#step' => 1,
        '#max' => 2,
        '#min' => 0,
      ],
      'theme' => [
        '#type'           => 'select',
        '#title'          => $this->t('Theme for Toast'),
        '#default_value'  => $config->get('theme'),
        '#description'    => $this->t('It can be light or dark'),
        '#options'        => [
          'dark' => $this->t('Dark'),
          'light' => $this->t('Light'),
        ],
      ],
      'rtl' => [
        '#type' => 'checkbox',
        '#title' => $this->t('RTL option'),
        '#default_value' => $config->get('rtl'),
      ],
      'resetOnHover' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Reset on hover'),
        '#description'    => $this->t('Reset the toast timeout while the cursor is on it.'),
        '#default_value' => $config->get('pauseOnHover'),
      ],
      'overlay' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Overlay'),
        '#description'    => $this->t('Enables display the Overlay layer on the page.'),
        '#default_value' => $config->get('overlay'),
      ],
      'overlayClose' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Overlay close'),
        '#description'    => $this->t('Allows to close toast clicking on the Overlay.'),
        '#default_value' => $config->get('overlay'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('izi_message.settings');

    $config
      ->set('titleSize', $form_state->getValue('titleSize'))
      ->set('messageSize', $form_state->getValue('messageSize'))
      ->set('theme', $form_state->getValue('theme'))
      ->set('timeout', $form_state->getValue('timeout'))
      ->set('maxWidth', $form_state->getValue('maxWidth'))
      ->set('drag', $form_state->getValue('drag'))
      ->set('close', $form_state->getValue('close'))
      ->set('closeOnEscape', $form_state->getValue('closeOnEscape'))
      ->set('closeOnClick', $form_state->getValue('closeOnClick'))
      ->set('rtl', $form_state->getValue('rtl'))
      ->set('displayMode', $form_state->getValue('displayMode'))
      ->set('pauseOnHover', $form_state->getValue('pauseOnHover'))
      ->set('resetOnHover', $form_state->getValue('resetOnHover'))
      ->set('progressBar', $form_state->getValue('progressBar'))
      ->set('overlay', $form_state->getValue('overlay'))
      ->set('overlayClose', $form_state->getValue('overlayClose'))
      ->set('animateInside', $form_state->getValue('animateInside'))
      ->set('transitionIn', $form_state->getValue('transitionIn'))
      ->set('transitionOut', $form_state->getValue('transitionOut'))
      ->set('transitionInMobile', $form_state->getValue('transitionInMobile'))
      ->set('transitionOutMobile', $form_state->getValue('transitionOutMobile'))
      ->set('position', $form_state->getValue('position'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Default toast open animation.
   *
   * @return array
   *   options for animation toast.
   */
  public function getInAnimationOptions() {
    return [
      'bounceInLeft' => 'bounceInLeft',
      'bounceInRight' => 'bounceInRight',
      'bounceInUp' => 'bounceInUp',
      'bounceInDown' => 'bounceInDown',
      'fadeIn' => 'fadeIn',
      'fadeInDown' => 'fadeInDown',
      'fadeInUp' => 'fadeInUp',
      'fadeInLeft' => 'fadeInLeft',
      'fadeInRight' => 'fadeInRight' ,
      'flipInX' => 'flipInX',
    ];
  }

  /**
   * Default toast close animation.
   *
   * @return array
   *   options for animation toast.
   */
  public function getOutAnimationOptions() {
    return [
      'fadeOut' => 'fadeOut',
      'fadeOutDown' => 'fadeOutDown',
      'fadeOutUp' => 'fadeOutUp',
      'fadeOutLeft' => 'fadeOutLeft',
      'fadeOutRight' => 'fadeOutRight',
      'flipOutX' => 'flipOutX',
    ];
  }

  /**
   * Where it will be shown.
   *
   * @return array
   *   options for position field.
   */
  public function getPotitionOptions() {
    return [
      'bottomRight' => $this->t('Bottom Right'),
      'bottomLeft'  => $this->t('Bottom left'),
      'topRight'    => $this->t('Top Right'),
      'topLeft'     => $this->t('Top Left'),
      'topCenter'   => $this->t('Top Center'),
      'bottomCenter' => $this->t('Bottom Center'),
      'center'      => $this->t('Center'),
    ];
  }

}
