<?php

namespace Drupal\alt_login\Plugin\AltLoginMethod;

use Drupal\alt_login\AltLoginMethodInterface;
use Drupal\alt_login\Attribute\AltLoginMethod;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Entity\Query\Sql\Condition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation for logging in with the uid as an alias.
 */
#[AltLoginMethod(
  id: 'uid',
  label: new TranslatableMarkup('User ID'),
  description: new TranslatableMarkup("Drupal's database key")
)]
class Uid extends Username implements AltLoginMethodInterface {

  /**
   * {@inheritDoc}
   */
  function applies($alias) : bool {
    $num = (int)$alias;
    return $num == $alias;
  }

  /**
   * {@inheritDoc}
   */
  function getUserFromAlias($alias) {
    if ($user = User::load($alias)) {
      return $user;
    }
  }

  /**
   * {@inheritDoc}
   */
  function getAlias(UserInterface $user) : string {
    return $user->id();
  }

  /**
   * {@inheritDoc}
   */
  function entityQuery(Condition $or_group, $match) {
    if (is_numeric($match)) {
      $or_group->condition('uid', $match);
    }
  }
}
