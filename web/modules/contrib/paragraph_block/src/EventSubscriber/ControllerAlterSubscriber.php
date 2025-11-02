<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to alter controller output.
 */
class ControllerAlterSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a ControllerAlterSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(
    private readonly ModuleHandlerInterface $moduleHandler
  ) {}

  /**
   * Alters the controller output.
   *
   * @param \Symfony\Component\HttpKernel\Event\ViewEvent $event
   *   The view event.
   */
  public function onView(ViewEvent $event): void {
    if ($this->moduleHandler->moduleExists('layout_builder')) {
      $route = $event->getRequest()->attributes->get('_route');
      // Alters the layout_builder.choose_inline_block controller's build.
      if ($route === 'layout_builder.choose_inline_block') {
        $build = $event->getControllerResult();
        // Filter out the paragraph_block from the list.
        foreach ($build['links']['#links'] as $delta => $link) {
          $plugin_parts = explode(':', $link['url']->getRouteParameters()['plugin_id']);
          if (in_array('paragraph_block', $plugin_parts, TRUE)) {
            unset($build['links']['#links'][$delta]);
          }
        }
        $event->setControllerResult($build);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Priority > 0 so that it runs before the controller output
    // is rendered by \Drupal\Core\EventSubscriber\MainContentViewSubscriber.
    $events[KernelEvents::VIEW][] = ['onView', 50];
    return $events;
  }

}
