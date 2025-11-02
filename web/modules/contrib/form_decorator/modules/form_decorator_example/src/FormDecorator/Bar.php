<?php

declare(strict_types=1);

namespace Drupal\form_decorator_example\FormDecorator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_decorator\FormDecoratorBase;
use Drupal\form_decorator\Attribute\FormDecorator;

/**
 * Adds a info test text to the login form.
 */
#[FormDecorator('form_user_login_form_alter', 1)]
final class Bar extends FormDecoratorBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ...$args) {
    $form = $this->inner->buildForm($form, $form_state, ...$args);
    if (!isset($form['test_string']['#markup'])) {
      $form['test_string'] = [
        '#type' => 'markup',
        '#markup' => '',
      ];
    }
    $form['test_string']['#markup'] .= 'Bar';
    return $form;
  }

}
