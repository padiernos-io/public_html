<?php

namespace Drupal\media_name\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_name.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_name_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('media_name.settings');
    $form['file_name_override'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('File name override'),
      '#description' => $this->t('Enable file name replacement when the media name is the same as the original file name. Changing the file will then also update the Media name.'),
      '#default_value' => $config->get('file_name_override'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('media_name.settings')
      ->set('file_name_override', $form_state->getValue('file_name_override'))
      ->save();
  }

}
