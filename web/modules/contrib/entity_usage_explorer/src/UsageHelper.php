<?php

namespace Drupal\entity_usage_explorer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides custom Twig functions for the Entity Usage Explorer module.
 */
class UsageHelper extends AbstractExtension {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The entity usage service.
   *
   * @var \Drupal\entity_usage_explorer\UsageService
   */
  protected UsageService $entityUsageService;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * Constructs a UsageHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\entity_usage_explorer\UsageService $entity_usage_service
   *   The entity usage service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, UsageService $entity_usage_service, LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityUsageService = $entity_usage_service;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('loadParagraphParent', [$this, 'loadParagraphParent']),
      new TwigFunction('getEntityLabel', [$this, 'getEntityLabel']),
      new TwigFunction('getEntity', [$this, 'getEntity']),
      new TwigFunction('getCanonicalPath', [$this, 'getCanonicalPath']),
      new TwigFunction('getLanguageName', [$this, 'getLanguageName']),
      new TwigFunction('getEntityUrl', [$this, 'getEntityUrl']),
    ];
  }

  /**
   * Loads the parent entity of a paragraph.
   *
   * @param int $pid
   *   The paragraph ID.
   * @param string $langcode
   *   The language code.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The parent entity if found, NULL otherwise.
   */
  public function loadParagraphParent(int $pid, ?string $langcode = NULL): ?EntityInterface {
    $paragraph = $this->entityTypeManager->getStorage('paragraph')->load($pid);

    if (!$paragraph instanceof EntityInterface || !method_exists($paragraph, 'getParentEntity')) {
      return NULL;
    }

    $parent = $paragraph->getParentEntity();

    return $parent instanceof EntityInterface
    ? $this->getEntity($parent->getEntityTypeId(), $parent->id(), $langcode)
    : NULL;
  }

  /**
   * Returns the value of 'label' entity key of entity type.
   *
   * @param string $entity_type
   *   The entity type name.
   * @param int $entity_id
   *   The entity id.
   * @param string $langcode
   *   The language code.
   *
   * @return string|null
   *   'label' entity key value if exists, NULL otherwise.
   */
  public function getEntityLabel(string $entity_type, int $entity_id, ?string $langcode = NULL): ?string {
    $content_entity_definitions = $this->entityUsageService->loadContentEntityTypes(TRUE);
    $label_key = $content_entity_definitions[$entity_type]['label_key'] ?? NULL;

    if (!$label_key) {
      return NULL;
    }

    $entity = $this->getEntity($entity_type, $entity_id, $langcode);
    return $entity?->{$label_key}->value ?? NULL;
  }

  /**
   * Returns the entity data.
   *
   * @param string $entity_type
   *   The entity type name.
   * @param int $entity_id
   *   The entity id.
   * @param string $langcode
   *   The language code.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   entity data if found, NULL otherwise.
   */
  public function getEntity(string $entity_type, int $entity_id, ?string $langcode = NULL): ?EntityInterface {
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);

    if ($entity && $langcode && $entity->hasTranslation($langcode)) {
      return $entity->getTranslation($langcode);
    }

    return $entity;
  }

  /**
   * Returns the canonical path of entity.
   *
   * @param string $entity_type
   *   The entity type name.
   * @param int $entity_id
   *   The entity id.
   *
   * @return string|null
   *   Canonical path if exists, NULL otherwise.
   */
  public function getCanonicalPath(string $entity_type, int $entity_id): ?string {
    $content_entity_definitions = $this->entityUsageService->loadContentEntityTypes(TRUE);
    $canonical_url = $content_entity_definitions[$entity_type]['canonical_url'];
    return $canonical_url ? str_replace("{{$entity_type}}", $entity_id, $canonical_url) : NULL;
  }

  /**
   * Returns the language name for a given language code.
   *
   * @param string $langcode
   *   The language code (e.g., 'en', 'fr').
   *
   * @return string
   *   The human-readable language name (e.g., 'English', 'French'), or the
   *   language code itself if not found.
   */
  public function getLanguageName($langcode) {
    $languages = $this->languageManager->getLanguages();
    return isset($languages[$langcode]) ? $languages[$langcode]->getName() : $langcode;
  }

  /**
   * Returns the canonical URL of entity as string, with optional translation.
   *
   * @param string $entity_type
   *   The entity type ID (e.g., 'node', 'user', 'paragraph').
   * @param int $entity_id
   *   The entity ID.
   * @param string|null $langcode
   *   (optional) The language code (e.g., 'en', 'fr'). Defaults to NULL.
   *
   * @return string|null
   *   The canonical URL of entity as a string, or NULL if entity does not exist
   *   or has no canonical route.
   */
  public function getEntityUrl(string $entity_type, int $entity_id, ?string $langcode = NULL): ?string {
    $entity = $this->getEntity($entity_type, $entity_id, $langcode);

    if ($entity instanceof EntityInterface && $entity->hasLinkTemplate('canonical')) {
      $url = $entity->toUrl('canonical');

      if ($langcode) {
        $url = $url->setOption('language', $this->languageManager->getLanguage($langcode));
      }

      return $url->toString();
    }

    return NULL;
  }

}
