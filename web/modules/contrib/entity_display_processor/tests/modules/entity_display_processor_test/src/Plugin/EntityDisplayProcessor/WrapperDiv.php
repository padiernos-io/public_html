<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor_test\Plugin\EntityDisplayProcessor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_display_processor\Attribute\EntityDisplayProcessor;
use Drupal\entity_display_processor\Plugin\EntityDisplayProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[EntityDisplayProcessor(
  'wrapper_div',
  new TranslatableMarkup('Wrapper div'),
)]
class WrapperDiv implements EntityDisplayProcessorInterface, ContainerFactoryPluginInterface {

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
    // More advanced wrapping could be done with '#theme_wrappers' or
    // '#post_render'.
    $element['#prefix'] = '<div class="wrapper">';
    $element['#suffix'] = '</div>';
    return $element;
  }

}
