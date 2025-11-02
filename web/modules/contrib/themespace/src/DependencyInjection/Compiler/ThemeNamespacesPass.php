<?php

namespace Drupal\themespace\DependencyInjection\Compiler;

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\Extension\ExtensionDiscovery;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * A dependency injection container compiler pass to add theme namespaces.
 *
 * Compiler passes are run after the DrupalKernel compileContainer() but before
 * attachSythetic() adds the namespaces to the class loader. This allows us to
 * add relevant theme namespaces to "container.namespaces" and the class loader.
 *
 * Note: Theme namespaces added here have the namespace prefix starting with
 * "Drupal\Theme\<theme name>" in order to avoid conflict with modules and
 * allow \Drupal\themespace\Discovery\ProviderTypedAnnotatedClassDiscovery to
 * differentiate between module and theme classes by namespace.
 *
 * @see \Drupal\Core\DrupalKernel::compileContainer()
 * @see \Drupal\Core\DrupalKernel::attachSynthetic()
 * @see \Drupal\themespace\Discovery\ProviderTypedAnnotatedClassDiscovery::getProviderInfoFromNamespace()
 */
class ThemeNamespacesPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container): void {
    // Get the list of enabled extensions, including the enabled themes.
    $extensions = BootstrapConfigStorageFactory::get()->read('core.extension');

    // Can be skipped if there are no enabled themes.
    if (!empty($extensions['theme'])) {
      $appRoot = $container->getParameter('app.root');
      $listing = new ExtensionDiscovery($appRoot);
      $themeData = $listing
        ->setProfileDirectories([])
        ->scan('theme');

      $themespaces = [];

      // Only add namespaces for enabled themes which have a "/src" folder.
      foreach ($extensions['theme'] as $name => $weight) {
        if (!empty($themeData[$name])) {
          $themePath = $themeData[$name]->getPath() . '/src';

          if (file_exists($themePath)) {
            $themespaces["Drupal\\$name"] = $themePath;
          }
        }
      }
      $container->setParameter('container.themespaces', $themespaces);
    }
  }

}
