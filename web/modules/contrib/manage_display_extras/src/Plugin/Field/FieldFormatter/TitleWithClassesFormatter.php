<?php

namespace Drupal\manage_display_extras\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\manage_display\Plugin\Field\FieldFormatter\TitleFormatter;

/**
 * Plugin implementation of the 'Title with classes' formatter.
 *
 * @FieldFormatter(
 *   id = "title_with_classes",
 *   label = @Translation("Title with classes"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TitleWithClassesFormatter extends TitleFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'classes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Clases for the link or the tag when there is no link'),
      '#description' => $this->t('Separated by spaces'),
      '#default_value' => $this->getSetting('classes'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $output = [];
    $parent = $items->getParent()->getValue();
    $classes = explode(' ', $this->getSetting('classes'));
    foreach ($items as $item) {
      $tagAttributes = [];
      $text = $item->getValue()['value'];
      if ($this->getSetting('link_to_entity')) {
        $url = $parent->isNew() ? Url::fromRoute('<front>') : $parent->toUrl();
        $attributes = $url->getOption('attributes');
        foreach ($classes as $class) {
          $attributes['class'][] = $class;
        }
        $attributes = $url->setOption('attributes', $attributes);
        $text = Link::fromTextAndUrl($text, $url)->toString();
      }
      else {
        $tagAttributes = ['class' => $classes];
      }
      $output[] = [
        '#type' => 'html_tag',
        '#tag' => $this->getSetting('tag'),
        '#value' => $text,
        '#attributes' => $tagAttributes,
      ];
    }
    return $output;
  }

}
