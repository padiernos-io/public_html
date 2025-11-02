<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\Access;

use Drupal\block_content\BlockContentAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\paragraph_block\ParagraphBlockServiceInterface;

/**
 * Access control handler to restrict standalone creation of paragraph blocks.
 */
class ParagraphBlockContentAccessControlHandler extends BlockContentAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    if ($entity_bundle === ParagraphBlockServiceInterface::BLOCK_TYPE) {
      return AccessResult::forbidden('This block type is not meant to be used outside a paragraph block context.');
    }

    return parent::checkCreateAccess($account, $context, $entity_bundle);
  }

}
