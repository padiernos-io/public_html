<?php

namespace Drupal\sdc_component_library\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ComponentPluginManager;
use Drupal\Core\Theme\ThemeManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides route responses for component previews.
 */
class ComponentsController extends ControllerBase {
  use StringTranslationTrait;

  /**
   * Constructs a ComponentsController object.
   *
   * @param \Drupal\Core\Theme\ComponentPluginManager $pluginManager
   *   The component plugin manager service from Drupal Core.
   * @param \Drupal\Core\Theme\ThemeManager $themeManager
   *   The theme manager service.
   */
  public function __construct(
    private readonly ComponentPluginManager $pluginManager,
    private readonly ThemeManager $themeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('plugin.manager.sdc'),
      $container->get('theme.manager'),
    );
  }

  /**
   * Provides a page with a list of component previews.
   *
   * @return array
   *   A render array containing the component previews.
   */
  public function content(): array {
    try {
      // Get all available components from the plugin manager.
      $allComponents = $this->pluginManager->getDefinitions();

      // Find components, including those missing .story.twig files.
      $components = $this->findComponentsWithWarnings($allComponents);

      return $this->buildRenderArray($components);
    }
    catch (\RuntimeException $e) {
      return [
        '#markup' => $this->t('Error: @message', ['@message' => $e->getMessage()]),
      ];
    }
  }

  /**
   * Find components and provide a warning if .story.twig is missing.
   *
   * @param array $allComponents
   *   An array of all components.
   *
   * @return array
   *   An array of components data with warnings if necessary.
   */
  private function findComponentsWithWarnings(array $allComponents): array {
    $components = [];
    $activeTheme = $this->themeManager->getActiveTheme()->getName();

    foreach ($allComponents as $componentDefinition) {
      $componentName = $componentDefinition['machineName'];

      // Search for the .story.twig file.
      $storyTwigFile = $this->findStoryTwigFile($componentDefinition);

      // Add component data with or without template file.
      $components[] = $storyTwigFile
        ? $this->buildComponentData($storyTwigFile, $componentDefinition)
        : [
          'title' => $componentDefinition['name'] ?? $this->t('Unnamed Component'),
          'machine_name' => $componentDefinition['machineName'] ?? $this->t('Unnamed Component'),
          'provider' => $componentDefinition['provider'] ?? '',
          'description' => $componentDefinition['description'] ?? '',
          'group' => $componentDefinition['group'] ?? '',
          'props' => $componentDefinition['props']['properties'] ?? [],
          'example_data' => $componentDefinition['example_data'] ?? [],
          'type' => $componentName,
          'twig_template' => NULL,
          'missing_template' => TRUE,
        ];
    }

    // Sort components by provider (active theme first), group, and title.
    usort($components, function ($a, $b) use ($activeTheme) {
      // Prioritize components where the provider matches the active theme.
      $aIsActiveTheme = $a['provider'] === $activeTheme ? 0 : 1;
      $bIsActiveTheme = $b['provider'] === $activeTheme ? 0 : 1;

      return [
        $aIsActiveTheme, $a['provider'], $a['group'], $a['title'],
      ] <=> [
        $bIsActiveTheme, $b['provider'], $b['group'], $b['title'],
      ];
    });

    return $components;
  }

  /**
   * Search for the .story.twig file for a component.
   *
   * @param array $component
   *   The machine name of the component.
   *
   * @return string|null
   *   The full path to the .story.twig file if found, otherwise null.
   */
  private function findStoryTwigFile(array $component): ?string {
    $componentPath = $component['path'];
    $twig_file_path = $componentPath . DIRECTORY_SEPARATOR . $component['machineName'] . '.story.twig';

    if (file_exists($twig_file_path)) {
      return $this->makePathRelativeToRoot($twig_file_path);
    }

    return NULL;
  }

  /**
   * Get the relative path from the component.
   *
   * @param string $absolutePath
   *   The full file path of the component.
   *
   * @return string
   *   The relative path.
   */
  public function makePathRelativeToRoot(string $absolutePath): string {
    return str_replace(DRUPAL_ROOT . DIRECTORY_SEPARATOR, '', $absolutePath);
  }

  /**
   * Build component data from file paths.
   *
   * @param string $relativePath
   *   The relative file path of the component.
   * @param array $componentDefinition
   *   The component definition data.
   *
   * @return array
   *   The component data array.
   */
  private function buildComponentData(string $relativePath, array $componentDefinition): array {
    return [
      'title' => $componentDefinition['name'] ?? $this->t('Unnamed Component'),
      'machine_name' => $componentDefinition['machineName'] ?? $this->t('Unnamed Component'),
      'provider' => $componentDefinition['provider'] ?? '',
      'description' => $componentDefinition['description'] ?? '',
      'group' => $componentDefinition['group'] ?? '',
      'props' => $componentDefinition['props']['properties'] ?? [],
      'example_data' => $componentDefinition['example_data'] ?? [],
      'libraryDependencies' => $componentDefinition['libraryDependencies'] ?? [],
      'type' => explode('/', $relativePath)[0],
      'theme' => 'component_preview_item',
      'twig_template' => $relativePath,
    ];
  }

  /**
   * Build the render array for component previews.
   *
   * @param array $components
   *   An array of component data.
   *
   * @return array
   *   The render array.
   */
  private function buildRenderArray(array $components): array {
    return [
      '#theme' => 'component_preview',
      '#components' => $components,
      '#attached' => [
        'library' => [
          'sdc_component_library/axe_core',
          'sdc_component_library/components_preview',
        ],
      ],
    ];
  }

}
