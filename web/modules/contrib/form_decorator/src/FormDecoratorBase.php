<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\BaseFormIdInterface;

/**
 * Base class for form decorators.
 */
class FormDecoratorBase extends PluginBase implements FormInterface, FormDecoratorInterface {

  /**
   * The decorated form.
   *
   * @var \Drupal\Core\Form\FormInterface
   */
  protected FormInterface $inner;

  /**
   * {@inheritdoc}
   */
  public function setInner(FormInterface $inner): void {
    $this->inner = $inner;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    return in_array($this->getPluginDefinition()['hook'], $this->getHooks($this->inner));
  }

  /**
   * Get all hooks that could be implemented to alter the given form.
   *
   * @param \Drupal\Core\Form\FormInterface $form_arg
   *   The form that could be altered.
   *
   * @return string[]
   *   The hooks that can be used.
   */
  protected function getHooks(FormInterface $form_arg): array {
    $hooks = ['form_alter'];
    if ($form_arg instanceof BaseFormIdInterface) {
      $hooks[] = 'form_' . $form_arg->getBaseFormId() . '_alter';
    }
    $hooks[] = 'form_' . $form_arg->getFormId() . '_alter';

    return $hooks;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->inner->getFormId();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ...$args) {
    return $this->inner->buildForm($form, $form_state, ...$args);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return $this->inner->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return $this->inner->submitForm($form, $form_state);
  }

  /**
   * Passes through all unknown calls onto the decorated object.
   */
  public function __call($method, $args) {
    return call_user_func_array([$this->inner, $method], $args);
  }

}
