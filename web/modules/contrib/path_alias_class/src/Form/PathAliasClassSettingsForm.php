<?php

namespace Drupal\path_alias_class\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure path_alias_class settings for this site.
 */
class PathAliasClassSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['path_alias_class.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'path_alias_class_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('path_alias_class.settings');

    $form['add_path'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Path'),
      '#description' => $this->t('Check this to add the current Path as a class to the body tag.'),
      '#default_value' => $config->get('add_path'),
    ];

    $form['add_alias'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add Alias'),
      '#description' => $this->t('Check this to add the current Path Alias as a class to the body tag.'),
      '#default_value' => $config->get('add_alias'),
    ];

    $form['path_alias_class_custom_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS Class'),
      '#description' => $this->t('Enter a custom CSS class to add to the body tag.'),
      '#default_value' => $config->get('path_alias_class_custom_class'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('path_alias_class.settings')
      ->set('add_path', $form_state->getValue('add_path'))
      ->set('add_alias', $form_state->getValue('add_alias'))
      ->set('path_alias_class_custom_class', $form_state->getValue('path_alias_class_custom_class'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
