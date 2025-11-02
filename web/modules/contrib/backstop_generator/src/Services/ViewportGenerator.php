<?php

namespace Drupal\backstop_generator\Services;

use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Service for generating backstop_viewport entities from theme breakpoints.
 */
class ViewportGenerator {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The breakpoint manager.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * Constructs a new BackstopViewportGenerator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ThemeHandlerInterface $theme_handler,
    LoggerChannelFactoryInterface $logger_factory,
    BreakpointManagerInterface $breakpoint_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->themeHandler = $theme_handler;
    $this->logger = $logger_factory->get('backstop_generator');
    $this->breakpointManager = $breakpoint_manager;
  }

  /**
   * Creates backstop_viewport entities for all breakpoints in a theme.
   *
   * @param string $theme_id
   *   The theme ID.
   *
   * @return array
   *   Array of created entity IDs.
   */
  public function createViewportsForTheme(string $theme_id): string|array {
    $created_entities = [];

    try {
      // Get theme breakpoints.
      $breakpoints = $this->breakpointManager->getBreakpointsByGroup($theme_id);

      if (empty($breakpoints)) {
        $this->logger->notice('No breakpoints found for theme @theme', ['@theme' => $theme_id]);
        return 'no breakpoints found';
      }

      $storage = $this->entityTypeManager->getStorage('backstop_viewport');

      // Process each breakpoint and create a viewport entity.
      foreach ($breakpoints as $breakpoint_id => $breakpoint) {
        // Extract the width from the media query.
        $media_query = !empty($breakpoint->getMediaQuery()) ? $breakpoint->getMediaQuery() : '(min-width:300px)';
        preg_match('/min-width:\s*(\d+)px/', $media_query, $min_matches);
        preg_match('/max-width:\s*(\d+)px/', $media_query, $max_matches);

        // Determine the width for this viewport.
        $width = NULL;
        if (!empty($max_matches[1])) {
          $width = (int) $max_matches[1];
        }
        elseif (!empty($min_matches[1])) {
          $width = (int) $min_matches[1];
        }

        if ($width) {
          // Create a machine-safe name from the breakpoint label.
          $label = $breakpoint->getLabel();
          $machine_name = preg_replace('/[^a-z0-9_]+/', '_', strtolower($label));
          $machine_name = $theme_id . '_' . $machine_name;

          // Check if an entity with this name already exists.
          $existing = $storage->loadByProperties(['id' => $machine_name]);
          if (!empty($existing)) {
            $this->logger->notice('Viewport for @breakpoint already exists.', ['@breakpoint' => $machine_name]);
            continue;
          }

          // Create the viewport entity.
          $viewport = $storage->create([
            'id' => $machine_name,
            'label' => $theme_id . ': ' . $label,
            'width' => $width,
            'height' => 900,
          ]);
          $viewport->save();

          $created_entities[] = $machine_name;
          $this->logger->notice('Created viewport @name with width @width', [
            '@name' => $machine_name,
            '@width' => $width,
          ]);
        }
      }
    }
    catch (\Exception $e) {
      $this->logger->error('Error creating viewports for theme @theme: @message', [
        '@theme' => $theme_id,
        '@message' => $e->getMessage(),
      ]);
    }

    return $created_entities;
  }

}
