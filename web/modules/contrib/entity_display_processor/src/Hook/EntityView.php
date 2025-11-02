<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Hook;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\entity_display_processor\EntityDisplayProcessorManager;

class EntityView {

  public function __construct(
    protected readonly EntityDisplayProcessorManager $entityDisplayProcessorManager,
  ) {}

  /**
   * Implements hook_entity_view_alter().
   */
  #[Hook('entity_view_alter')]
  public function entityViewAlter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display): void {
    $conf = $display->getThirdPartySetting('entity_display_processor', 'processor');
    if (!$conf) {
      return;
    }
    try {
      $processor = $this->entityDisplayProcessorManager->getInstance($conf);
    }
    catch (PluginException) {
      // @todo Report this somehow.
      return;
    }
    $build = $processor->process($build, $entity);
  }

}
