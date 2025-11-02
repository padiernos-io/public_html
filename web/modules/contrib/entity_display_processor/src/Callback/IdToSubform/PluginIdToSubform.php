<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Callback\IdToSubform;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Factory\FactoryInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Gets a plugin from a plugin manager to build the subform.
 */
class PluginIdToSubform implements IdToSubformInterface {

  use DependencySerializationTrait;

  /**
   * Constructor.
   *
   * @param \Drupal\Component\Plugin\Factory\FactoryInterface $pluginFactory
   *   Plugin factory, typically a plugin manager.
   */
  public function __construct(
    protected readonly FactoryInterface $pluginFactory,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function __invoke(int|string $id, array $settings, SubformStateInterface $form_state): array {
    try {
      $plugin = $this->pluginFactory->createInstance($id, $settings);
    }
    catch (PluginException) {
      // @todo Rethink the failure behavior.
      return [];
    }
    if ($plugin instanceof PluginFormInterface) {
      $subform = [];
      return $plugin->buildConfigurationForm($subform, $form_state)
        ?: [];
    }
    return [];
  }

}
