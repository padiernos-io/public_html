<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url as UrlObject;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for [path] tokens.
 *
 * @PatternTokenDependencyProvider(
 *   type = "url",
 * )
 */
class Url extends PatternTokenDependencyProviderBase {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->languageManager = $container->get('language_manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    $url = $data['url'];
    if (isset($options['langcode'])) {
      $language = $this->languageManager->getLanguage($options['langcode']);
      $url->setOption('language', $language);
    }
    $path = $this->getPathFromUrl($url);

    foreach ($tokens as $name => $original) {
      if ($name === 'path' && $alias = $this->getPathAlias($path)) {
        $dependencies->addEntity($alias);
      }
    }
  }

  /**
   * Get the path from a URL object.
   */
  protected function getPathFromUrl(UrlObject $url): string {
    $path = '/';

    // Ensure the URL is routed to avoid throwing an exception.
    if ($url->isRouted()) {
      $path .= (clone $url)
        ->setAbsolute(FALSE)
        ->setOption('fragment', NULL)
        ->getInternalPath();
    }

    return $path;
  }

}
