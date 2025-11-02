<?php

namespace Drupal\cookieinformation;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Cookieinformation Cookie Policy' Block.
 */
class CookieInformationBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cookieinformation language service.
   *
   * @var \Drupal\cookieinformation\LanguageService
   */
  protected $languageService;

  /**
   * The cookieinformation visibility service.
   *
   * @var \Drupal\cookieinformation\VisibilityService
   */
  protected $visibilityService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    LanguageService $language_service,
    VisibilityService $visibility_service,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container that provides the necessary dependencies.
   * @param array $configuration
   *   An array of configuration settings for the plugin.
   * @param string $plugin_id
   *   The unique identifier for the plugin instance.
   * @param mixed $plugin_definition
   *   An array containing the plugin definition, which may include additional
   *   metadata and settings.
   *
   * @return static
   *   Returns an instance of the plugin, allowing for method chaining.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('cookieinformation.language_service'),
      $container->get('cookieinformation.visibility_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->visibilityService->checkAll()) {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [];
  }

}
