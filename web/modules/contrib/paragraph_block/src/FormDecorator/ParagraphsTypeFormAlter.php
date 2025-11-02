<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\FormDecorator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_decorator\FormDecoratorBase;

/**
 * Plugin implementation for "paragraphs_type_edit_form".
 *
 * @FormDecorator()
 */
class ParagraphsTypeFormAlter extends FormDecoratorBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    return in_array('form_paragraphs_type_edit_form_alter', $this->getHooks($this->inner), TRUE)
      || in_array('form_paragraphs_type_add_form_alter', $this->getHooks($this->inner), TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ...$args): array {
    $form = parent::buildForm($form, $form_state, ...$args);

    /** @var \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type */
    $paragraphs_type = $this->getEntity();

    $form['paragraph_block_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Paragraph block settings'),
      '#description' => $this->t('Configure this paragraph type to be used with paragraph block module.'),
      '#tree' => TRUE,
      '#open' => $paragraphs_type->getThirdPartySetting('paragraph_block', 'status', FALSE),
    ];

    $form['paragraph_block_settings']['status'] = [
      '#title' => $this->t('Enable'),
      '#type' => 'checkbox',
      '#default_value' => $paragraphs_type->getThirdPartySetting('paragraph_block', 'status'),
      '#description' => $this->t('Use this paragraph type as a block.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): void {
    /** @var \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type */
    $paragraphs_type = $this->getEntity();

    $paragraph_block_settings = $form_state->getValue('paragraph_block_settings');
    foreach ($paragraph_block_settings as $key => $paragraph_block_setting) {
      $paragraphs_type->setThirdPartySetting('paragraph_block', $key, $paragraph_block_setting);
    }
    $this->inner->setEntity($paragraphs_type);

    $this->inner->save($form, $form_state);
  }

}
