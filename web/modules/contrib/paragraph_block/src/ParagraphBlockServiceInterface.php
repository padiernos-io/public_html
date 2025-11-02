<?php

declare(strict_types=1);

namespace Drupal\paragraph_block;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the interface for the Paragraph Block service.
 */
interface ParagraphBlockServiceInterface {

  /**
   * The block type used to store the paragraph.
   *
   * @var string
   */
  public const BLOCK_TYPE = 'paragraph_block';

  /**
   * The field name used to store the paragraph.
   *
   * @var string
   */
  public const FIELD_NAME = 'field_paragraph_block_paragraph';

  /**
   * Retrieves the available paragraph block types.
   *
   * @return array
   *   An array of paragraph block types.
   */
  public function getParagraphBlockTypes(): array;

  /**
   * Alters the block element after it is built.
   *
   * @param array $element
   *   The renderable array of the element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The modified element.
   */
  public static function elementAfterBuild(array $element, FormStateInterface $form_state);

}
