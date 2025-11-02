<?php

namespace Drupal\backstop_generator;

use Drupal\backstop_generator\Form\BackstopScenarioFilterForm;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a listing of backstop scenarios.
 */
class BackstopScenarioListBuilder extends ConfigEntityListBuilder {

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new BackstopScenarioListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    RequestStack $request_stack,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
  ) {
    parent::__construct($entity_type, $storage);
    $this->requestStack = $request_stack;
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('ID');
    $header['bundle'] = $this->t('Bundle');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\backstop_generator\BackstopScenarioInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['bundle'] = $entity->get('bundle');

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    // Initialize an empty render array.
    $build = [];

    // Add the filter form directly to the render array.
    $build['filter_form'] = $this->formBuilder
      ->getForm(BackstopScenarioFilterForm::class);

    // Add the parent render array, which includes the entity listing.
    $build += $build + parent::render();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    // Get all entity IDs.
    $entity_ids = $this->entityTypeManager
      ->getStorage($this->entityTypeId)
      ->getQuery()
      ->accessCheck(TRUE)
      ->execute();

    // Check if a label filter is applied.
    $label_filter = $this->requestStack->getCurrentRequest()->query->get('label_filter');
    if (!empty($label_filter)) {
      // Load all entities and manually filter them based on the label.
      $entities = $this->entityTypeManager
        ->getStorage($this->entityTypeId)
        ->loadMultiple($entity_ids);

      // Filter entities manually.
      $filtered_entity_ids = [];
      foreach ($entities as $entity) {
        if (stripos($entity->label(), $label_filter) !== FALSE) {
          $filtered_entity_ids[] = $entity->id();
        }
      }
      return $filtered_entity_ids;
    }
    return $entity_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $storage = $this->getStorage();
    $query = $storage->getQuery();

    $request = $this->requestStack->getCurrentRequest();

    // Use query parameters first; fallback to session.
    $profile_filter = $request->query->get('profile_filter', '');
    $label_filter = $request->query->get('label_filter', '');

    $entity_ids = $query->execute();

    // Filter entities list based on the profile and label filters.
    foreach ($entity_ids as $entity_id) {
      $entity = $storage->load($entity_id);

      if (!empty($profile_filter)) {
        if (stripos($entity->id(), $profile_filter) === FALSE) {
          unset($entity_ids[$entity_id]);
        }
      }

      if (!empty($label_filter)) {
        if (stripos($entity->label(), $label_filter) === FALSE) {
          unset($entity_ids[$entity_id]);
        }
      }
    }

    return $storage->loadMultiple($entity_ids);
  }

}
