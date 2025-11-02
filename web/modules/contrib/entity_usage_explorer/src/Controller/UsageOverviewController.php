<?php

namespace Drupal\entity_usage_explorer\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_usage_explorer\UsageService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for entity usage overview page.
 */
class UsageOverviewController implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * Used to get entity usage data.
   *
   * @var \Drupal\entity_usage_explorer\UsageService
   */
  protected $entityUsageService;

  /**
   * Constructs a UsageOverviewController object.
   *
   * @param \Drupal\entity_usage_explorer\UsageService $entity_usage_service
   *   The entity usage service.
   */
  public function __construct(UsageService $entity_usage_service) {
    $this->entityUsageService = $entity_usage_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('entity_usage_explorer.usage')
    );
  }

  /**
   * Builds the usage overview page for a given entity.
   *
   * @param string $entity_type
   *   The machine name of the entity type.
   * @param int $entity_id
   *   The entity ID to retrieve usage data for.
   *
   * @return array
   *   A render array for the entity usage overview page.
   */
  public function getUsagePage($entity_type, $entity_id) {
    $data = $this->entityUsageService->getEntityUsage($entity_type, $entity_id);

    return [
      '#theme' => 'entity_usage_overview',
      '#target_entity_type' => $entity_type,
      '#target_entity_id' => $entity_id,
      '#data' => $data,
    ];
  }

  /**
   * Generates the title for the usage overview page.
   *
   * @param string $entity_type
   *   The machine name of the entity type.
   * @param int $entity_id
   *   The entity ID being analyzed.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated page title.
   */
  public function getTitle($entity_type, $entity_id): TranslatableMarkup {
    return $this->t("Usage statistics for: <em>@entity_type(@entity_id)</em>", [
      '@entity_type' => $entity_type,
      '@entity_id' => $entity_id,
    ]);
  }

}
