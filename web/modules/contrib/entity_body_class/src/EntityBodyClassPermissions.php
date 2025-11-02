<?php

namespace Drupal\entity_body_class;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for entity body class module.
 */
class EntityBodyClassPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EntityBodyClassPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of entity body class permissions.
   *
   * @return array
   *   An array of permissions for all plugins.
   */
  public function permissions() {
    $permissions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      if (in_array(ContentEntityInterface::class, class_implements($definition->getOriginalClass())) &&
        $definition->getLinkTemplate('canonical')
      ) {
        $label = $definition->getLabel() instanceof TranslatableMarkup ? $definition->getLabel()->render() : $definition->getLabel();
        $permissions["access {$definition->id()} body class field"] = [
          'title' => $this->t('Manage @entity_type body class field', ['@entity_type' => $label]),
        ];
      }
    }

    return $permissions;
  }

}
