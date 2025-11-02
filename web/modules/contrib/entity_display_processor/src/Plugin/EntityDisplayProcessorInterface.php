<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Plugin;

use Drupal\Core\Entity\EntityInterface;

/**
 * Modifies an entity render element.
 */
interface EntityDisplayProcessorInterface {

  /**
   * Modifies an entity render element.
   *
   * @param array $element
   *   Render array showing a part of an entity.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being displayed.
   *
   * @return array
   *   Modified render element.
   */
  public function process(array $element, EntityInterface $entity): array;

}
