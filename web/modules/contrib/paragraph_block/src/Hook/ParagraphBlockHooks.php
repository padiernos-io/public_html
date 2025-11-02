<?php

namespace Drupal\paragraph_block\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\paragraph_block\ParagraphBlockService;
use Drupal\paragraph_block\ParagraphBlockServiceInterface;

/**
 * Hook implementations for paragraph_block.
 */
class ParagraphBlockHooks {

  /**
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) {
    if (isset($entity_types['block_content'])) {
      // Use custom storage and access handlers for block content entities.
      $entity_types['block_content_type']->setHandlerClass('storage', 'Drupal\\paragraph_block\\Entity\\Storage\\CustomBlockContentTypeStorage');
      $entity_types['block_content']->setHandlerClass('storage', 'Drupal\\paragraph_block\\Entity\\Storage\\CustomBlockContentStorage');
      $entity_types['block_content']->setHandlerClass('access', 'Drupal\\paragraph_block\\Access\\ParagraphBlockContentAccessControlHandler');
    }
  }

  /**
   * Implements hook_block_type_form_alter().
   */
  #[Hook('block_type_form_alter')]
  public function blockTypeFormAlter(array &$form, FormStateInterface &$form_state, string $block_type) {
    if ($block_type === ParagraphBlockServiceInterface::BLOCK_TYPE) {
      // Add custom after-build method to paragraph field widget for UX.
      $form[ParagraphBlockServiceInterface::FIELD_NAME]['widget']['#after_build'][] = [
        ParagraphBlockService::class,
        'elementAfterBuild',
      ];
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter() for language_content_settings_form().
   */
  #[Hook('form_language_content_settings_form_alter')]
  public function formLanguageContentSettingsFormAlter(array &$form, FormStateInterface $form_state): void {
    $paragraph_block_types = \Drupal::service('paragraph_block.service')->getParagraphBlockTypeKeys();
    // Make sure paragraph_block types can't get content translation settings on
    // their own. They will follow the paragraph_block settings.
    foreach ($paragraph_block_types as $paragraph_block_type) {
      if (isset($form['settings']['block_content'][$paragraph_block_type])) {
        unset($form['settings']['block_content'][$paragraph_block_type]);
      }
    }
  }

}
