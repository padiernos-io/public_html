<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;

/**
 * Provides methods to extract information from menu links.
 */
trait MenuLinkEntityTrait {

  /**
   * Get the menu link entity of a menu link plugin.
   */
  protected function getMenuLinkEntity(MenuLinkInterface $menuLink, ?string $langcode = NULL): ?MenuLinkContentInterface {
    if ($menuLink instanceof MenuLinkContent) {
      $metadata = $menuLink->getPluginDefinition()['metadata'];
    }

    if ($menuLink instanceof MenuLinkInterface || $menuLink->getProvider() !== 'menu_link_content') {
      $metadata = $menuLink->getMetaData();
    }

    if (!isset($metadata['entity_id'])) {
      return NULL;
    }

    $entity = $this->entityTypeManager
      ->getStorage('menu_link_content')
      ->load($metadata['entity_id']);

    if (!$entity instanceof ContentEntityInterface) {
      return NULL;
    }

    if ($langcode && $entity->isTranslatable() && $entity->hasTranslation($langcode)) {
      return $entity->getTranslation($langcode);
    }

    return $entity;
  }

  /**
   * Get the referenced entity of a menu link.
   *
   * @param \Drupal\menu_link_content\MenuLinkContentInterface|\Drupal\Core\Menu\MenuLinkInterface $menuLink
   *   The menu link.
   * @param string|null $langcode
   *   The language code.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The referenced entity.
   */
  protected function getReferencedEntity($menuLink, ?string $langcode = NULL): ?EntityInterface {
    $url = $menuLink->getUrlObject();
    $language = $this->languageManager->getLanguage($langcode);
    $url->setOption('language', $language);

    return $this->urlEntityExtractor->getEntityByUrl($url);
  }

}
