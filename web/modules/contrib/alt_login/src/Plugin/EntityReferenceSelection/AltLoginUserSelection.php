<?php

namespace Drupal\alt_login\Plugin\EntityReferenceSelection;

use Drupal\user\Plugin\EntityReferenceSelection\UserSelection;
use Drupal\Core\Entity\Attribute\EntityReferenceSelection;
use Drupal\alt_login\AltLoginMethodManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides specific access control for the user entity type.
 */
#[EntityReferenceSelection(
  id: "default:altlogin",
  label: new TranslatableMarkup("AltLogin user display name"),
  entity_types: ["user"],
  group: "alt_login",
  weight: 2
)]
class AltLoginUserSelection extends UserSelection {

  /**
   * @var array
   */
  protected $activePlugins;

  /**
   * Constructor
   *
   * @param $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param $entity_type_manager
   * @param $module_handler
   * @param $current_user
   * @param $connection
   * @param $entity_field_manager
   * @param $entity_type_bundle_info
   * @param $entity_repository
   * @param AltLoginMethodManager $altlogin_method_manager
   */
  public function __construct(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $entity_type_manager,
    $module_handler,
    $current_user,
    $connection,
    $entity_field_manager,
    $entity_type_bundle_info,
    $entity_repository,
    AltLoginMethodManager $altlogin_method_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user, $connection, $entity_field_manager, $entity_type_bundle_info, $entity_repository);
    $this->activePlugins = $altlogin_method_manager->activePlugins();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('current_user'),
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('alt_login.method_manager'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @note This doesn't inherit from its ancestors because we're not presuming
   * to search on the user name.
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $query = $this->entityTypeManager->getStorage('user')->getQuery()->accessCheck(1);
    $query->addTag('user_access');
    $query->addTag('entity_reference');
    $query->addMetaData('entity_reference_selection_handler', $this);

    $configuration = $this->getConfiguration();
    // Add the sort option.
    if ($configuration['sort']['field'] !== '_none') {
      $query->sort($configuration['sort']['field'], $configuration['sort']['direction']);
    }
    if (!$configuration['include_anonymous']) {
      $query->condition('uid', 0, '<>');
    }
    if (!empty($configuration['filter']['role'])) {
      $query->condition('roles', $configuration['filter']['role'], 'IN');
    }
    if ($match) {
      $or = $query->orConditionGroup();
      foreach ($this->activePlugins as $plugin) {
        $plugin->entityQuery($or, $match);
      }
      $query->condition($or);
    }
    return $query;
  }

}
