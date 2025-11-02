<?php

declare(strict_types=1);

namespace Drupal\form_decorator_example\FormDecorator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\form_decorator\FormDecoratorBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\form_decorator\Attribute\FormDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Adds dependency injection to the user login form.
 */
#[FormDecorator('form_user_login_form_alter')]
final class DependencyInjection extends FormDecoratorBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructs a DependencyInjection object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ...$args) {
    $form = $this->inner->buildForm($form, $form_state, ...$args);
    $anonymous = $this->entityTypeManager->getStorage('user')->load(0);
    $form['info'] = [
      '#markup' => $this->t('If you are not logged in you work as @name.', ['@name' => $anonymous->getDisplayName()]),
    ];
    return $form;
  }

}
