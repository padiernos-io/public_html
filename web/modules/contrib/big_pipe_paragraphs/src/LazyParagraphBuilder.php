<?php

namespace Drupal\big_pipe_paragraphs;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Class LazyParagraphBuilder
 *
 * @package Drupal\big_pipe_paragraphs
 */
class LazyParagraphBuilder implements TrustedCallbackInterface {

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var array
   */
  private $config;

  /**
   * LazyParagraphBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    $this->entityTypeManager = $entityTypeManager;
    $this->config = $configFactory->get('big_pipe_paragraphs.settings')
      ->get('entity_type');
  }

  /**
   * Lazy build paragraph.
   *
   * @param int $paragraphId
   * @param string $viewMode
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function lazyBuild(int $paragraphId, $viewMode): array {
    $paragraphStorage = $this->entityTypeManager->getStorage('paragraph');
    $paragraphViewBuilder = $this->entityTypeManager->getViewBuilder('paragraph');

    return $paragraphViewBuilder->view($paragraphStorage->load($paragraphId), $viewMode);
  }

  /**
   * @param string $entityTypeId
   * @param string $fieldName
   * @param string $bundle
   *
   * @return bool
   */
  public function bundleEnabled(string $entityTypeId, string $fieldName, string $bundle): bool {
    return isset($this->config[$entityTypeId][$fieldName]['entity_bundles']) && in_array($bundle, $this->config[$entityTypeId][$fieldName]['entity_bundles'], TRUE);
  }

  /**
   * @param string $entityTypeId
   * @param string $fieldName
   *
   * @return int|null
   */
  public function getOffset(string $entityTypeId, string $fieldName): ?int {
    if (!isset($this->config[$entityTypeId][$fieldName])) {
      return NULL;
    }
    return (int) $this->config[$entityTypeId][$fieldName]['offset'];
  }

  /**
   * @param string $entityTypeId
   * @param string $fieldName
   *
   * @return array
   */
  public function getSkipBundles(string $entityTypeId, string $fieldName): array {
    if (!isset($this->config[$entityTypeId][$fieldName])) {
      return [];
    }
    return $this->config[$entityTypeId][$fieldName]['skip_bundles'];
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks(): array {
    return ['lazyBuild'];
  }

}
