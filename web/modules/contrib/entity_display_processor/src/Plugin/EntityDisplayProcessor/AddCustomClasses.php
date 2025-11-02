<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Plugin\EntityDisplayProcessor;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_display_processor\Attribute\EntityDisplayProcessor;
use Drupal\entity_display_processor\Plugin\EntityDisplayProcessorInterface;

#[EntityDisplayProcessor(
  'custom_classes',
  new TranslatableMarkup('Add custom classes'),
)]
class AddCustomClasses extends PluginBase implements EntityDisplayProcessorInterface, PluginFormInterface {

  use StringTranslationTrait;

  /**
   * Regular expression snippet to match a single class.
   *
   * The simplified validation allows classes starting with a letter or
   * underscore, followed by letters, numbers, underscores or dashes.
   */
  const string CLASS_PATTERN = '[a-zA-Z_][a-zA-Z0-9_\\-]*';

  /**
   * Regular expression snippet to match classes separated by space.
   *
   * Leading, trailing or double spaces are not allowed, to keep the config
   * clean.
   *
   * @see \Drupal\Core\Render\Element\FormElement::validatePattern()
   */
  const string CLASSES_PATTERN = self::CLASS_PATTERN . '( ' . self::CLASS_PATTERN . ')*';

  /**
   * {@inheritdoc}
   */
  public function process(array $element, EntityInterface $entity): array {
    $classes_str = $this->configuration['classes'] ?? '';
    $classes = explode(' ', $classes_str);
    foreach ($classes as $class) {
      $element['#attributes']['class'][] = $class;
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['classes'] = [
      '#title' => $this->t('Custom classes'),
      '#type' => 'textfield',
      '#required' => FALSE,
      '#default_value' => $this->configuration['classes'] ?? '',
      '#pattern' => self::CLASSES_PATTERN,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {}

}
