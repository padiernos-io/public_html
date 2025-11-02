<?php

declare(strict_types=1);

namespace Drupal\paragraph_block;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides services for managing paragraph blocks.
 */
class ParagraphBlockService implements ParagraphBlockServiceInterface {

  use StringTranslationTrait;

  /**
   * ParagraphBlockService constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $blockManager
   *   The block manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected readonly BlockManagerInterface $blockManager,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getParagraphBlockTypes(): array {
    $paragraphs_type_storage = $this->entityTypeManager->getStorage('paragraphs_type');
    $paragraphs_types = $paragraphs_type_storage->loadMultiple();

    $block_types = [];
    /** @var \Drupal\paragraphs\Entity\ParagraphsType $paragraphs_type */
    foreach ($paragraphs_types as $bundle => $paragraphs_type) {
      if ($paragraphs_type->getThirdPartySetting('paragraph_block', 'status')) {
        $block_types[$bundle] = $paragraphs_type;
      }
    }

    return $block_types;
  }

  /**
   * {@inheritdoc}
   */
  public function getParagraphBlockTypeKeys(): array {
    return array_keys($this->getParagraphBlockTypes());
  }

  /**
   * {@inheritdoc}
   */
  public static function elementAfterBuild(array $element, FormStateInterface $form_state): array {
    $entity = $element['#host'] ?? $form_state->getFormObject()->getEntity();

    // Clean up the widget for better UX.
    foreach ($entity->{ParagraphBlockServiceInterface::FIELD_NAME}->getValue() as $delta => $value) {
      unset($element[$delta]['top']['actions']['dropdown_actions']['remove_button']);
      unset($element[$delta]['top']['actions']['actions']['collapse_button']);
      unset($element[$delta]['top']['type']['label']);
      unset($element['header_actions']);
    }

    return $element;
  }

}
