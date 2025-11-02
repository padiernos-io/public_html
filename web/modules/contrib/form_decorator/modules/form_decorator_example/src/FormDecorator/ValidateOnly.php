<?php

declare(strict_types=1);

namespace Drupal\form_decorator_example\FormDecorator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_decorator\FormDecoratorBase;
use Drupal\form_decorator\Attribute\FormDecorator;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides additional validation for the user registration form.
 */
#[FormDecorator('form_user_register_form_alter')]
final class ValidateOnly extends FormDecoratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->inner->validateForm($form, $form_state);
    if (strlen($form_state->getValue('name')) < 7) {
      $form_state->setErrorByName('name', $this->t('Your username should have at least 7 characters.'));
    }
  }

}
