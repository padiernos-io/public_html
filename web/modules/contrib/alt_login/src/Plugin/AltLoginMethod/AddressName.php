<?php

namespace Drupal\alt_login\Plugin\AltLoginMethod;

use Drupal\alt_login\AltLoginMethodInterface;
use Drupal\alt_login\Attribute\AltLoginMethod;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\Query\Sql\Condition;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation for logging in with the address name as an alias.
 */
#[AltLoginMethod(
  id: 'address_name',
  label: new TranslatableMarkup('First + last name'),
  description: new TranslatableMarkup('The given name and family name from the address field. Warning, duplicate names could lead to login and other confusions.')
)]
class AddressName implements AltLoginMethodInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * The name of the address field on the user entity.
   * @var string
   */
  private $fieldName;

  /**
   * @var Connection
   */
  private $database;

  /**
   * @var EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var MessengerInterface
   */
  private $messenger;

  /**
   * @param EntityFieldManagerInterface $entity_field_manager
   * @param Connection $database
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param MessengerInterface $messenger
   */
  function __construct(EntityFieldManagerInterface $entity_field_manager, Connection $database, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->entityFieldManager = $entity_field_manager;
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   *
   * @param ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $container->get('entity_field.manager'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }


  /**
   * {@inheritDoc}
   */
  function dedupeAlias(UserInterface $user) : string {
    $alias = $this->getAlias($user);
    if (empty($alias)) {
      $this->messenger->addWarning($this->t('Neither given name nor family name provided in address field.'));
    }
    $uids = $this->getUids($alias);
    if (!$user->isNew()) {
      unset($uids[array_search($user->id(), $uids)]);
    }
    return !empty($uids);
  }

  /**
   * {@inheritDoc}
   */
  function applies($alias) : bool {
    // Pretty much any string could be valid
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  function getUserFromAlias($alias) {
    if ($uids = $this->getUids($alias)) {
      return User::load(reset($uids));
    }
  }

  /**
   * {@inheritDoc}
   */
  function getAlias(UserInterface $user) : string {
    $field_name = $this->fieldName();
    $given_name = $user->{$field_name}->given_name ?? '';
    $family_name = $user->{$field_name}->family_name ?? '';
    $parts = [trim($given_name), trim($family_name)];
    return implode(' ', array_filter($parts));
  }

  /**
   * Helper
   *
   * Get the name of the addressfield on the user.
   */
  function fieldName() {
    if (empty($this->fieldName)) {
      foreach ($this->entityFieldManager->getFieldDefinitions('user', 'user') as $field_name => $fieldInfo) {
        if ($fieldInfo->getType() == 'address') {
          $this->fieldName  = $field_name;
          break;
        }
      }
    }
    return $this->fieldName;
  }

  /**
   * {@inheritdoc}
   */
  private function getUids($alias) {
    $fname = $this->fieldName();
    $query = $this->database
      ->select('user__'.$fname, 'u')
      ->fields('u', ['entity_id'])
      ->where("CONCAT_WS(' ', {$fname}_given_name, {$fname}_family_name) = '$alias'");
    // Hopefully the database field isn't case sensitive.
    return $query->execute()->fetchCol();
  }

  /**
   * {@inheritDoc}
   */
  function entityQuery(Condition $or_group, $match) {
    $fname = $this->fieldName();
    list($first, $last) = explode(' ', $match) + [1 => ''];
    if (!$last) {
      $or_group->condition($fname.'.given_name', $first, 'STARTS_WITH');
      $or_group->condition($fname.'.family_name', $first, 'STARTS_WITH');
    }
    else {
      $and_group = $this->entityTypeManager->getStorage('user')->getQuery()->andConditionGroup();
      $and_group->condition($fname.'.given_name', substr($first, 0, 4), 'STARTS_WITH');
      $and_group->condition($fname.'.family_name', $last, 'STARTS_WITH');
      $or_group->condition($and_group);
    }
  }
}

