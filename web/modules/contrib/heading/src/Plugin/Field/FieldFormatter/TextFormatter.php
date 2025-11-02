<?php

namespace Drupal\heading\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin to format string & text fields as a heading.
 *
 * @FieldFormatter(
 *   id = "heading_text",
 *   label = @Translation("Heading"),
 *   field_types = {
 *     "string",
 *     "text"
 *   }
 * )
 */
class TextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['size' => 'h2'] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#default_value' => $this->getSetting('size'),
      '#options' => [
        'h1' => $this->t('Heading 1'),
        'h2' => $this->t('Heading 2'),
        'h3' => $this->t('Heading 3'),
        'h4' => $this->t('Heading 4'),
        'h5' => $this->t('Heading 5'),
        'h6' => $this->t('Heading 6'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t(
      'Heading (@size)',
      ['@size' => strtoupper($this->getSetting('size'))]
    );

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $size = $this->getSetting('size');

    $elements = [];
    /** @var \Drupal\text\Plugin\Field\FieldType\TextItem|\Drupal\Core\Field\Plugin\Field\FieldType\StringItem $item */
    foreach ($items as $delta => $item) {
      if (empty($item->value)) {
        continue;
      }

      $elements[$delta] = [
        '#type' => 'html_tag',
        '#tag' => $size,
        'child' => $this->viewElement($item),
      ];
    }

    return $elements;
  }

  /**
   * Get the proper render array for the given item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *    The field item to get the render array for.
   *
   * @return array
   *    The render array.
   */
  private function viewElement(FieldItemInterface $item): array {
    $fieldType = $this->fieldDefinition->getType();

    if ($fieldType === 'text') {
      return $this->viewText($item);
    }

    return $this->viewString($item);
  }

  /**
   * Create the render array for a string item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item to get the render array for.
   *
   * @return array
   *   The render array.
   *
   * @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter::viewValue()
   */
  private function viewString(FieldItemInterface $item): array
  {
    return [
      '#type' => 'inline_template',
      '#template' => '{{ value|nl2br }}',
      '#context' => [
        'value' => $item->getString(),
      ],
    ];
  }

  /**
   * Create the render array for a text item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item to get the render array for.
   *
   * @return array
   *   The render array.
   *
   * @see \Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter::viewElements()
   */
  private function viewText(FieldItemInterface $item): array
  {
    $value = $item->getValue();

    return [
      '#type' => 'processed_text',
      '#text' => $value['value'],
      '#format' => $value['format'],
      '#langcode' => $item->getLangcode(),
    ];
  }

}
