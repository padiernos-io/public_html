<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Change the form builder class.
 */
class FormDecoratorServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if (is_a($container->getDefinition('form_builder')->getClass(), FormBuilder::class, TRUE)) {
      $definition = $container->getDefinition('form_builder');
      $definition->setClass(FormDecoratorFormBuilder::class);
      $definition->addArgument(new Reference('plugin.manager.form_decorator'));
    }
  }

}
