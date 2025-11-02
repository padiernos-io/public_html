<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;

/**
 * Base class for content entity form decorators.
 */
class ContentEntityFormDecoratorBase extends EntityFormDecoratorBase implements ContentEntityFormInterface {

  /**
   * The inner entity form.
   *
   * @var \Drupal\Core\Entity\ContentEntityFormInterface
   */
  protected FormInterface $inner;

  /**
   * {@inheritdoc}
   */
  public function getFormDisplay(FormStateInterface $form_state) {
    return $this->inner->getFormDisplay($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function setFormDisplay(EntityFormDisplayInterface $form_display, FormStateInterface $form_state) {
    $this->inner->setFormDisplay($form_display, $form_state);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormLangcode(FormStateInterface $form_state) {
    return $this->inner->getFormLangcode($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultFormLangcode(FormStateInterface $form_state) {
    return $this->inner->isDefaultFormLangcode($form_state);
  }

}
