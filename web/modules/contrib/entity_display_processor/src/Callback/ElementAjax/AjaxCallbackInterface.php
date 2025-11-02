<?php

/**
 * @file
 */

declare(strict_types=1);
namespace Drupal\entity_display_processor\Callback\ElementAjax;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for $['#ajax']['callback'] callback objects.
 */
interface AjaxCallbackInterface {

  /**
   * Callback for ajax.
   *
   * @param array $form
   *   Complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return array
   *   Part of the form to be returned.
   */
  public function __invoke(array $form, FormStateInterface $form_state, Request $request): array;

}
