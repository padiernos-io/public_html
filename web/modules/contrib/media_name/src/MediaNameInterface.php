<?php

namespace Drupal\media_name;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface MediaNameInterface.
 */
interface MediaNameInterface {

  const MEDIA_NAME_ORIGINAL_VALUE = 'media_name_original_value';
  const FILE_NAME_ORIGINAL_VALUE = 'file_name_original_value';

  /**
   * Alters the Media edit form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function alterMediaEditForm(FormStateInterface $form_state);

  /**
   * Custom submit handler for the Media edit form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function mediaEditFormSubmitHandler(FormStateInterface $form_state);

}
