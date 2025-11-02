<?php

namespace Drupal\alt_login\Authentication\Provider;

use Symfony\Component\HttpFoundation\Request;

/**
 * HTTP Basic authentication provider which accepts user id & emails as well as username
 */
class BasicAuth extends \Drupal\basic_auth\Authentication\Provider\BasicAuth {

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    $alias = $request->headers->get('PHP_AUTH_USER');
    $request->headers->set('PHP_AUTH_USER', alt_login_convert_alias($alias));
    return parent::authenticate($request);
  }

}
