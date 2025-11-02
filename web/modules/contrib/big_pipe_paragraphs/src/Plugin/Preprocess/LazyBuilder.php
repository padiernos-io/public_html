<?php

namespace Drupal\big_pipe_paragraphs\Plugin\Preprocess;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\preprocess\Annotation\Preprocess;
use Drupal\preprocess\PreprocessPluginBase;

/**
 * Provides a preprocess for paragraph field to implement lazybuilding.
 *
 * @Preprocess(
 *   id = "big_pipe_paragraphs.lazy_builder",
 *   hook = "field"
 * )
 *
 * @package Drupal\synetic_paragraph\Plugin\Preprocess\Paragraph
 */
class LazyBuilder extends PreprocessPluginBase {

  /**
   * @inheritDoc
   */
  public function preprocess(array $variables): array {
    $element = $variables['element'];
    if ($element['#field_type'] !== 'entity_reference_revisions') {
      return $variables;
    }

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $fieldItemList */
    $fieldItemList = $element['#items'];
    $targetType = (string) $fieldItemList->getFieldDefinition()
      ->getFieldStorageDefinition()
      ->getSetting('target_type');

    if ($targetType !== 'paragraph') {
      return $variables;
    }

    /** @var \Drupal\big_pipe_paragraphs\LazyParagraphBuilder $lazyBuilderManager */
    $lazyBuilderManager = Drupal::service('big_pipe_paragraphs.lazy_builder');

    if (!($element['#object'] instanceof EntityInterface) || !$lazyBuilderManager->bundleEnabled($element['#object']->getEntityTypeId(), $element['#field_name'], $element['#object']->bundle())) {
      return $variables;
    }

    $offset = $lazyBuilderManager->getOffset($element['#object']->getEntityTypeId(), $element['#field_name']);
    $skip = $lazyBuilderManager->getSkipBundles($element['#object']->getEntityTypeId(), $element['#field_name']);

    foreach ($variables['items'] as $delta => &$item) {
      /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
      $paragraph = $item['content']['#paragraph'];
      $view_mode = !empty($item['content']['#view_mode']) ? $item['content']['#view_mode'] : 'default';
      if ($delta < $offset || in_array($paragraph->bundle(), $skip, TRUE)) {
        continue;
      }

      $item['content'] = [
        '#lazy_builder' =>
          [
            'big_pipe_paragraphs.lazy_builder:lazyBuild',
            [$paragraph->id(), $view_mode],
          ],
        '#create_placeholder' => TRUE,
      ];
    }


    return $variables;
  }

}
