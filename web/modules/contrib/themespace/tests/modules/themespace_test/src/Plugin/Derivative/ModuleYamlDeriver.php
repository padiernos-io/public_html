<?php

namespace Drupal\themespace_test\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create associated entity local tasks for quick access to owning association.
 */
class ModuleYamlDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The ID of the plugin the deriver is implementing.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Create a new local task menu deriver for associated entities.
   *
   * @param string $base_plugin_id
   *   The plugin ID of the deriver definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager) {
    $this->basePluginId = $base_plugin_id;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($baseDefinition) {
    /** @var \Drupal\themespace_test\Plugin\TestPluginDefinition */
    $base = $baseDefinition;

    $this->derivatives = [];

    $derived = clone $base;
    $derived->setDeriver(NULL);
    $this->derivatives['derived'] = $derived;

    return $this->derivatives;
  }

}
