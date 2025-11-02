<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\form_decorator\Attribute\FormDecorator;
use Drupal\form_decorator\Annotation\FormDecorator as FormDecoratorAnnotation;

/**
 * FormDecorator plugin manager.
 */
final class FormDecoratorPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\form_decorator\FormDecoratorPluginManager object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('FormDecorator', $namespaces, $module_handler, FormDecoratorInterface::class, FormDecorator::class, FormDecoratorAnnotation::class);
    $this->alterInfo('form_decorator_info');
    $this->setCacheBackend($cache_backend, 'form_decorator_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();

    // Sort the decorators by weight.
    uasort($definitions, [SortArray::class, 'sortByWeightElement']);
    return $definitions;
  }

}
