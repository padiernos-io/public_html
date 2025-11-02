<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Core\Form\FormInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for form_decorator plugins.
 */
interface FormDecoratorInterface extends PluginInspectionInterface {

  /**
   * Set the decorated form object.
   *
   * @param \Drupal\Core\Form\FormInterface $inner
   *   The decorated form.
   */
  public function setInner(FormInterface $inner): void;

  /**
   * Check if a form decorator shall decorate the specified form.
   *
   * @return bool
   *   TRUE, if it should decorate the form.
   */
  public function applies(): bool;

}
