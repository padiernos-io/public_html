<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor_test\Plugin\EntityDisplayProcessor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_display_processor\Attribute\EntityDisplayProcessor;
use Drupal\entity_display_processor\Plugin\EntityDisplayProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin to wrap a rendered entity in a div container.
 *
 * This is an alternative implementation, which creates nested render elements.
 * This is a proof of concept for other possible plugins.
 */
#[EntityDisplayProcessor(
  'wrapper_div_alt',
  new TranslatableMarkup('Wrapper div, alternative implementation'),
)]
class WrapperDivAlt implements EntityDisplayProcessorInterface, ContainerFactoryPluginInterface {

  public function __construct(
    protected readonly RendererInterface $renderer,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $container->get(RendererInterface::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(array $element, EntityInterface $entity): array {
    $this->clearPreRenderCallbacks($element);
    return [
      'content' => [
        '#type' => 'container',
        '#attributes' => ['class' => ['alternative-wrapper']],
        'content' => $element,
      ],
    ];
  }

  /**
   * Removes #pre_render callbacks to prevent recursion.
   *
   * @param array $element
   *   Element to alter.
   */
  protected function clearPreRenderCallbacks(array &$element): void {
    $callbacks = $element['#pre_render'] ?? [];
    if (!$callbacks) {
      return;
    }
    foreach ($callbacks as $delta => $callback) {
      unset($callbacks[$delta]);
      if (is_array($callback)
        && $callback[1] === 'build'
        && $callback[0] instanceof EntityViewBuilderInterface
      ) {
        // Don't unset other #pre_render callbacks after this one.
        $element['#pre_render'] = $callbacks;
        break;
      }
    }
  }

}
