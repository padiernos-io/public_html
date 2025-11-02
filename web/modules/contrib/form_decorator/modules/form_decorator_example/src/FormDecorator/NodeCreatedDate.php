<?php

declare(strict_types=1);

namespace Drupal\form_decorator_example\FormDecorator;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\form_decorator\Attribute\FormDecorator;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_decorator\ContentEntityFormDecoratorBase;
use Drupal\node\NodeInterface;

/**
 * Adds a created date picker to the node form.
 */
#[FormDecorator('form_node_form_alter')]
final class NodeCreatedDate extends ContentEntityFormDecoratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ...$args) {
    $form = $this->inner->buildForm($form, $form_state, ...$args);
    $node = $this->getEntity();
    assert($node instanceof NodeInterface);
    $created_time = time();
    if (!$this->getEntity()->isNew()) {
      $created_time = $node->getCreatedTime();
    }

    $form['created_datepicker'] = [
      '#title' => $this->t('Created date'),
      '#type' => 'datetime',
      '#default_value' => DrupalDateTime::createFromTimestamp($created_time),
      '#weight' => 100,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $node = $this->getEntity();
    assert($node instanceof NodeInterface);
    $node->setCreatedTime($form_state->getValue('created_datepicker')->getTimestamp());
    return $this->inner->save($form, $form_state);
  }

}
