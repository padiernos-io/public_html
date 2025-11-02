<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Callback\ElementAjax;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Callback to use in ajax forms.
 *
 * This is a regular object, so it is fully serializable.
 */
class AjaxReplaceCallback implements AjaxCallbackInterface {

  /**
   * Constructor.
   *
   * @param list<string> $parents
   *   Array keys to retrieve the form element.
   */
  public function __construct(
    protected readonly array $parents,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function __invoke(array $form, FormStateInterface $form_state, Request $request): array {
    return NestedArray::getValue($form, $this->parents);
  }

}
