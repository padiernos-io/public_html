<?php

namespace Drupal\pathauto_update\Plugin\PatternTokenDependencyProvider;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\path_alias\PathAliasInterface;
use Drupal\pathauto_update\PathAliasDependencyCollectionInterface;
use Drupal\pathauto_update\PatternTokenDependencyProviderBase;
use Drupal\url_entity\UrlEntityExtractorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dependencies for [join-path] tokens.
 *
 * @PatternTokenDependencyProvider(
 *   type = "array",
 * )
 */
class ArrayTokenDependencyProvider extends PatternTokenDependencyProviderBase {

  use MenuLinkEntityTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected RouteProviderInterface $routeProvider;

  /**
   * The URL entity extractor.
   *
   * @var \Drupal\url_entity\UrlEntityExtractorInterface
   */
  protected UrlEntityExtractorInterface $urlEntityExtractor;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->languageManager = $container->get('language_manager');
    $instance->routeProvider = $container->get('router.route_provider');
    $instance->urlEntityExtractor = $container->get('url_entity.extractor');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function addDependencies(array $tokens, array $data, array $options, PathAliasDependencyCollectionInterface $dependencies): void {
    $array = $data['array'];
    $langcode = $options['langcode'] ?? $this->languageManager->getCurrentLanguage()->getId();

    foreach ($tokens as $name => $original) {
      if ($name !== 'join-path') {
        continue;
      }

      foreach ($array as $item) {
        $linkEntity = $this->getMenuLinkEntity($item, $langcode);
        if (!$linkEntity instanceof MenuLinkContentInterface) {
          continue;
        }

        $dependencies->addEntity($linkEntity);

        $referencedEntity = $this->getReferencedEntity($linkEntity, $langcode);
        if (!$referencedEntity instanceof EntityInterface) {
          continue;
        }

        $pathAlias = $this->getPathAliasByEntity($referencedEntity);
        if (!$pathAlias instanceof PathAliasInterface) {
          continue;
        }

        $dependencies->addEntity($pathAlias);
      }
    }
  }

}
