<?php

namespace Drupal\patreon_user\Plugin\Block;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Config;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\patreon\PatreonServiceInterface;
use Drupal\patreon_user\PatreonUserService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Provides a 'PatreonUserBlock' block.
 *
 * @Block(
 *  id = "patreon_user_block",
 *  admin_label = @Translation("Patreon user block"),
 * )
 */
class PatreonUserBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new PatreonUserBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\patreon\PatreonServiceInterface $patreonUserApi
   *   The API User Service.
   * @param \Drupal\Core\Config\Config $config
   *   The module config.
   * @param int $loginMethod
   *   The current login setting.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected readonly PatreonServiceInterface $patreonUserApi,
    protected Config $config,
    protected int $loginMethod,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $configFactory = $container->get('config.factory');

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('patreon_user.api'),
      $configFactory->getEditable('patreon.settings'),
      $configFactory->getEditable('patreon_user.settings')->get('patreon_user_registration')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account): AccessResultForbidden|AccessResultAllowed|AccessResultInterface {
    if ($account->isAnonymous()) {
      return AccessResult::allowed()
        ->addCacheContexts(['user.roles:anonymous']);
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [];

    if ($this->loginMethod !== PatreonUserService::PATREON_USER_NO_LOGIN) {

      $url = $this->patreonUserApi->authoriseAccount(FALSE);

      $build['patreon_user_block'] = [
        '#title' => $this->t('Login via Patreon'),
        '#type' => 'link',
        '#url' => $url,
      ];
    }

    return $build;
  }

  /**
   * {@inheritDoc}
   */
  public function getCacheTags(): array {
    $tags = [
      'config:patreon_user.patreon_user_registration',
    ];
    if ($this->patreonUserApi->getReturnPath()) {
      $tags[] = 'user.roles';
    }

    return Cache::mergeTags(parent::getCacheTags(), $tags);
  }

}
