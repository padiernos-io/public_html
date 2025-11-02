<?php

namespace Drupal\alt_login;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the module
 */
class Settings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alt_login_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form= parent::buildForm($form, $form_state);
    $config = $this->config('alt_login.settings');
    $form['aliases'] = [
      '#title' => $this->t('Allow login with'),
      '#type' => 'checkboxes',
      '#options' => \Drupal::service('alt_login.method_manager')->getOptions(),
      '#default_value' => (array)$config->get('aliases'),
      '#weight' => 1,
      '#required' => TRUE
    ];
    foreach (\Drupal::service('alt_login.method_manager')->getDefinitions() as $key => $def){
      if (isset($form['aliases']['#options'][$key])) {
        $form['aliases'][$key]['#description'] = $def['description'];
      }
    }

    $form['display'] = [
      '#title' => $this->t('Display user name'),
      // TODO there seems to be some field token functionality seriously missing from drupal core
      '#description' => $this->t('Tokens available: @tokens.', ['@tokens' => '[user:uid] [user:account-name]']),
      '#type' => 'textfield',
      '#placeholder' => "[user:name]",
      '#default_value' => $config->get('display'),
      '#element_validate' => [[$this, 'validateDisplayTemplate']],
      '#weight' => 3
    ];
    $form['display_anon'] = [
      '#title' => $this->t('Display user name to anonymous users'),
      '#description' => $this->t('Tokens available: @tokens.', ['@tokens' => '[user:uid]']),
      '#type' => 'textfield',
      '#default_value' => $config->get('display_anon'),
      //'#required' => TRUE,
      '#weight' => 3
    ];
    if (\Drupal::moduleHandler()->moduleExists('token')) {
       $form['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['user'],
        '#weight' => 4,
      ];
      unset($form['display']['#description']);
      unset($form['display_anon']['#description']);
    }
    else {
      $form['token_tree'] = [
        '#markup' => $this->t('Install the token module for more options'),
        '#weight' => 4
      ];
    }

     $form['warning'] = [
      '#markup' => t('Warning: Changes to these settings could confuse existing users!'),
      '#weight' => 10
    ];
    return $form;
  }


  /**
   * Element validatio callback.
   */
  public function validateDisplayTemplate(&$element, FormStateInterface $form_state) {
    if (is_numeric(strpos($element['#value'], '[user:display-name]'))) {
       $form_state->setError($element, $this->t('[user:display-name] would create recursion problems here!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $login_modes = $form_state->getValue('aliases');
    $this->config('alt_login.settings')
      ->set('display', $form_state->getValue('display'))
      ->set('display_anon', $form_state->getValue('display_anon'))
      ->set('aliases', array_filter($login_modes))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['alt_login.settings'];
  }


}
