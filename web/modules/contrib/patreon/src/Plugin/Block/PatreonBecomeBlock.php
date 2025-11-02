<?php

namespace Drupal\patreon\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\patreon\PatreonServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'PatreonBecomeBlock' block.
 *
 * @Block(
 *  id = "patreon_become_block",
 *  admin_label = @Translation("Patreon Become a Patron block"),
 * )
 */
class PatreonBecomeBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Created the become Patron block.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\patreon\PatreonServiceInterface $service
   *   The Patreon Service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   A module handler service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected readonly PatreonServiceInterface $service,
    protected readonly ModuleHandlerInterface $moduleHandler,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Creates the Become Patron block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container interface.
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin configuration.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('patreon.api'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['minimum_amount'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum amount'),
      '#description' => $this->t('The minimum pledge in cents you wish to ask for in a pledge.'),
      '#default_value' => $config['minimum_amount'] ?? 0,
    ];

    if ($this->moduleHandler->moduleExists('patreon_user')) {
      $form['log_user_in'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Log user in'),
        '#description' => $this->t('Create an account on the site for the user and log them in after pledging.'),
        '#default_value' => $config['log_user_in'] ?? FALSE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();
    $this->configuration['minimum_amount'] = $values['minimum_amount'];

    if ($values['log_user_in']) {
      $this->configuration['log_user_in'] = TRUE;
    }
    else {
      $this->configuration['log_user_in'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];

    $config = $this->getConfiguration();

    if ($link = $this->service->getSignUpLink($config['minimum_amount'], $config['log_user_in'])) {
      $build['patreon_become_block'] = $link->toRenderable();
    }

    return $build;
  }

}
