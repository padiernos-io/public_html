<?php

declare(strict_types=1);

namespace Drupal\paragraph_block\EventSubscriber;

use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\paragraph_block\ParagraphBlockServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Make sure fake paragraph bundles don't land in config.
 */
class ConfigSubscriber implements EventSubscriberInterface {

  /**
   * ConfigSubscriber constructor.
   *
   * @param \Drupal\paragraph_block\ParagraphBlockServiceInterface $paragraphBlockService
   *   The paragraph block service.
   */
  public function __construct(
    protected readonly ParagraphBlockServiceInterface $paragraphBlockService
  ) {}

  /**
   * Prevents fake paragraph bundle configs from being exported.
   *
   * We do this by replacing any `block_content.type.*` dependency for
   * paragraph-block bundles with `paragraphs.paragraphs_type.*`.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The configuration event.
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    $config = $event->getConfig();
    // Make sure we have both keys to work with.
    $dependencies = (array) $config->get('dependencies') + [
      'config' => [],
      'module' => [],
    ];

    $original = $dependencies['config'];
    $filtered = [];
    $changed = FALSE;

    foreach ($original as $dependency) {
      $parts = explode('.', $dependency);
      // Look for block_content.type.<bundle> entries that match our
      // paragraph-blocks.
      if (
        count($parts) === 3 &&
        $parts[0] === 'block_content' &&
        $parts[1] === 'type' &&
        in_array($parts[2], $this->paragraphBlockService->getParagraphBlockTypeKeys(), TRUE)
      ) {
        // Replace the dependencies with paragraph configs.
        $bundle = $parts[2];
        $filtered[] = "paragraphs.paragraphs_type.{$bundle}";
        $changed = TRUE;
        continue;
      }
      // Keep everything else.
      $filtered[] = $dependency;
    }

    if ($changed) {
      // Re-index and dedupe.
      $dependencies['config'] = array_values(array_unique($filtered));
      // Ensure our module shows up exactly once.
      $dependencies['module'] = array_values(array_unique(array_merge(
        $dependencies['module'],
        ['paragraph_block', 'paragraphs']
      )));

      $config
        ->set('dependencies', $dependencies)
        ->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ConfigEvents::SAVE => ['onConfigSave', 300],
    ];
  }

}
