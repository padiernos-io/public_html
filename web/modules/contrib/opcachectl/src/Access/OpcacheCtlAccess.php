<?php

namespace Drupal\opcachectl\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks access for displaying configuration translation page.
 */
class OpcacheCtlAccess implements AccessInterface {

  /**
   * List of IP addresses allowed to access protected opcachectl routes.
   *
   * Example configuration in settings.php:
   *
   * ```
   * $settings['opcachectl_reset_remote_addresses'] = ['127.0.0.1', '::1'];
   * ```
   *
   * @var array
   */
  protected $authorizedAddresses = [];

  /**
   * Token required to access protected opcachectl routes.
   *
   * This Token will only be checked, if the request is made from a client
   * with an address not listed in $authorizedAddresses.
   *
   * Generate token via
   *
   * ```
   * #> cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1
   * ```
   *
   * settings.php:
   * $settings['opcachectl_reset_token'] = 'somerandomvalue';
   *
   * @var string
   */
  protected $requestToken;

  /**
   * Constructs a new OpcacheCtlController object.
   */
  public function __construct() {
    $this->requestToken = trim(Settings::get("opcachectl_reset_token") ?? '');
    $this->authorizedAddresses = Settings::get('opcachectl_reset_remote_addresses', []);
  }

  /**
   * A custom access check for protected opcachectl routes.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Result of access check.
   */
  public function access(Request $request): AccessResult {
    $ip = $request->getClientIp();
    $is_localhost_ip = $ip == "127.0.0.1" || $ip == "::1";
    if ($ip == $_SERVER['SERVER_ADDR'] || $is_localhost_ip || $_SERVER['HTTP_HOST'] == 'localhost') {
      // Always allow access to "same machine".
      return AccessResult::allowed();
    }

    if (!empty($this->authorizedAddresses)) {
      // Allow access if client IP is whitelisted.
      if (is_array($this->authorizedAddresses)) {
        if (in_array($ip, $this->authorizedAddresses)) {
          return AccessResult::allowed();
        }
      }
      else {
        if ($ip == $this->authorizedAddresses) {
          return AccessResult::allowed();
        }
      }
    }
    if (!empty($this->requestToken) && $request->query->has('token')) {
      // Allow access if request contains correct token.
      $token = trim($request->query->get('token') ?? '');
      if ($token == $this->requestToken) {
        return AccessResult::allowed();
      }
    }

    // Access denied by default.
    return AccessResult::forbidden();
  }

}
