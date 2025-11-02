<?php

declare(strict_types=1);

namespace Drupal\heading\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type "heading" containing a heading type and text.
 *
 * @FieldType(
 *   id = "heading",
 *   label = @Translation("Heading"),
 *   description = @Translation("Adds a text field with customizable heading type."),
 *   category = "plain_text",
 *   module = "heading",
 *   default_formatter = "heading",
 *   default_widget = "heading",
 *   column_groups = {
 *     "size" = {
 *       "label" = @Translation("Size"),
 *       "translatable" = FALSE
 *     },
 *     "text" = {
 *       "label" = @Translation("Text"),
 *       "translatable" = TRUE
 *     },
 *   },
 * )
 */
class HeadingItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'size' => [
          'type' => 'char',
          'length' => 2,
          'not null' => FALSE,
          'description' => 'The heading size (h1-h6).',
        ],
        'text' => [
          'type' => 'varchar',
          'length' => 255,
          'not null' => FALSE,
          'description' => 'The text within the heading.',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $size = DataDefinition::create('string');
    $size->setLabel(new TranslatableMarkup('Heading: Size'));
    $size->setDescription(new TranslatableMarkup('The size (h1, h2, ...) of the heading.'));

    $text = DataDefinition::create('string');
    $text->setLabel(new TranslatableMarkup('Heading: Text'));
    $text->setDescription(new TranslatableMarkup('The text (content) of the heading.'));

    return [
      'size' => $size,
      'text' => $text,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    $size = $this->get('size')->getValue();
    $text = $this->get('text')->getValue();
    return empty($size) && empty($text);
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    $settings = [
      'label' => 'Heading',
      'allowed_sizes' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
    ];
    return $settings + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state): array {
    $element = [];
    $default_settings = self::defaultFieldSettings();

    $default_label_settings = $this->getSetting('label');
    $default_label = !empty($default_label_settings)
      ? $default_label_settings
      : $default_settings['label'];
    $element['label'] = [
      '#type' => 'textfield',
      '#title' => new TranslatableMarkup('Label'),
      '#description' => new TranslatableMarkup('Set the form label for the text field of the heading.'),
      '#default_value' => $default_label,
    ];

    $allowed_sizes_settings = $this->getSetting('allowed_sizes');
    $default_allowed_sizes = is_array($allowed_sizes_settings) && !empty($allowed_sizes_settings)
      ? $allowed_sizes_settings
      : $default_settings['allowed_sizes'];
    $element['allowed_sizes'] = [
      '#type' => 'checkboxes',
      '#title' => new TranslatableMarkup('Allowed sizes'),
      '#description' => new TranslatableMarkup('Limit the allowed heading sizes.'),
      '#options' => $this->getSizes(),
      '#default_value' => $default_allowed_sizes,
    ];

    return $element;
  }

  /**
   * Get all possible sizes.
   *
   * @return array
   *   The heading size labels keyed by their size (h1-h6).
   */
  protected function getSizes(): array {
    return [
      'h1' => new TranslatableMarkup('Heading 1'),
      'h2' => new TranslatableMarkup('Heading 2'),
      'h3' => new TranslatableMarkup('Heading 3'),
      'h4' => new TranslatableMarkup('Heading 4'),
      'h5' => new TranslatableMarkup('Heading 5'),
      'h6' => new TranslatableMarkup('Heading 6'),
    ];
  }

}
