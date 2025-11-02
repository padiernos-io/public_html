<?php

namespace Drupal\themespace;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\themespace\DependencyInjection\Compiler\ThemeNamespacesPass;

/**
 * Service provider class for altering the dependency injection container.
 *
 * This service provider registers a compiler pass to add theme namespaces
 * to the "container.namespaces" container parameter. This occurs at a point in
 * the container building that allows the added namespaces to be included in
 * the class loader's PSR4 namespace (in other words theme classes are
 * resolvable).
 *
 * @see \Drupal\themespace\Compiler\ThemeNamespacesPass::process()
 */
class ThemespaceServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    $container->addCompilerPass(new ThemeNamespacesPass());
  }

}
