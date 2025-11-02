<?php

namespace Drupal\alt_login;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies basic auth service to accept other usernames.
 */
class AltLoginServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($def = $container->getDefinition('basic_auth.authentication.basic_auth')) {
      $def->setClass('Drupal\alt_login\Authentication\Provider\BasicAuth');
    }
  }

}
