<?php

namespace Drupal\cookieinformation;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Session\AccountProxy;
use Drupal\path_alias\AliasManagerInterface;

/**
 * The Cookieinformation visibility service.
 */
class VisibilityService {

  /**
   * The router admin context service.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $routerAdminContext;

  /**
   * The path current service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $pathCurrent;

  /**
   * The path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * The path matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Cookieinformation config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a \Drupal\cookieinformation\VisibilityService.
   *
   * @param \Drupal\Core\Routing\AdminContext $router_admin_context
   *   The router admin context service.
   * @param \Drupal\Core\Path\CurrentPathStack $path_current
   *   The path current service.
   * @param \Drupal\path_alias\AliasManagerInterface $path_alias_manager
   *   The path alias manager service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The path matcher service.
   * @param \Drupal\Core\Session\AccountProxy $current_user
   *   The current user service.
   */
  public function __construct(
    AdminContext $router_admin_context,
    CurrentPathStack $path_current,
    AliasManagerInterface $path_alias_manager,
    PathMatcherInterface $path_matcher,
    ConfigFactoryInterface $config_factory,
    AccountProxy $current_user,
  ) {
    $this->routerAdminContext = $router_admin_context;
    $this->pathCurrent = $path_current;
    $this->pathAliasManager = $path_alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->config = $this->configFactory->get('cookieinformation.settings');
  }

  /**
   * Check if cookie information consent popup is enabled.
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkEnabled() {
    if (!empty($this->config->get('enable_popup')) && $this->config->get('enable_popup')) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Check if the user has permission to see the page without the consent popup.
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkPermissions() {
    if ((int) $this->currentUser->id() !== 1 && $this->currentUser->hasPermission('disable cookie information consent')) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Checks for uid 1.
   *
   * Check if the current user is uid = 1 and consent popup
   * is disabled for this user.
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkUser1() {
    if ((int) $this->currentUser->id() === 1 && !empty($this->config->get('exclude_uid_1')) && $this->config->get('exclude_uid_1')) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Check if consent popup is disabled on admin routes.
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkAdminRoutes() {
    if ($this->config->get('exclude_admin')) {
      if ($this->routerAdminContext->isAdminRoute()) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Check exclude paths.
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkExcludePaths() {
    if (!empty($this->config->get('exclude_paths'))) {
      $exclude_paths = $this->config->get('exclude_paths');
      $path = $this->pathCurrent->getPath();
      $url_alias_path = $this->pathAliasManager->getAliasByPath($path);
      $path_match = $this->pathMatcher->matchPath($path, $exclude_paths);
      $path_match_url_alias = $this->pathMatcher->matchPath($url_alias_path, $exclude_paths);
      $path_match = $path_match || $path_match_url_alias;
      if ($path_match) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Check all "in once".
   *
   * @return bool
   *   TRUE if yes, FALSE if no.
   */
  public function checkAll() {
    if ($this->checkEnabled() && $this->checkPermissions() && $this->checkUser1() && $this->checkAdminRoutes() && $this->checkExcludePaths()) {
      return TRUE;
    }

    return FALSE;
  }

}
