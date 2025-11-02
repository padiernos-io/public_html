<?php

namespace Drupal\alt_login\Plugin\AltLoginMethod;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\alt_login\AltLoginMethodInterface;
use Drupal\alt_login\Attribute\AltLoginMethod;
use Drupal\user\UserInterface;
use Drupal\Core\Entity\Query\Sql\Condition;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation for logging in with the user name as an alias.
 */
#[AltLoginMethod(
  id: 'username',
  label: new TranslatableMarkup('Username'),
  description: new TranslatableMarkup('N.B. if not checked, username field is hidden from user profile and populated with the email.')
)]
class Username implements AltLoginMethodInterface, ContainerFactoryPluginInterface {

  private $entityTypeManager;

  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   */
  function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  function dedupeAlias(UserInterface $user) :string {
    // This is checked by the user module and database anyway.
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  function applies($alias) : bool {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  function getUserFromAlias($alias) {
    $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $alias]);
    if (count($users)) {
      return reset($users);
    }
  }

  /**
   * {@inheritDoc}
   */
  function getAlias(UserInterface $user) : string {
    return strtolower($user->getAccountName());
  }

  /**
   * {@inheritDoc}
   */
  function entityQuery(Condition $or_group, $match) {
    $or_group->condition('name', $match, 'STARTS_WITH');
  }

}
