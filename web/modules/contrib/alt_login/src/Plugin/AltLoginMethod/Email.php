<?php

namespace Drupal\alt_login\Plugin\AltLoginMethod;

use Drupal\alt_login\AltLoginMethodInterface;
use Drupal\alt_login\Attribute\AltLoginMethod;
use Drupal\user\UserInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\Sql\Condition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation for logging in with the email as an alias.
 */
#[AltLoginMethod(
  id: 'email',
  label: new TranslatableMarkup('Email'),
  description: new TranslatableMarkup("Login with the user's email")
)]
class Email extends Username implements AltLoginMethodInterface, ContainerFactoryPluginInterface {

  /**
   * @var EmailValidatorInterface
   */
  private $emailValidator;

  /**
   * @var EntityTypeManagerInterface
   */
  private $entityTypeManager;


  /**
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param EmailValidatorInterface $email_validator
   */
  function __construct(EntityTypeManagerInterface $entity_type_manager, EmailValidatorInterface $email_validator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $email_validator;
  }

  /**
   * @param ContainerInterface $container
   * @param array $configuration
   * @param type $plugin_id
   * @param type $plugin_definition
   * @return \static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static (
      $container->get('entity_type.manager'),
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritDoc}
   */
  function applies($alias) : bool {
    return $this->emailValidator->isValid($alias);
  }


  /**
   * {@inheritDoc}
   */
  function getUserFromAlias($alias) {
    // Hopefully this isn't case sensitive.
    $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['mail' => $alias]);
    if (count($users)) {
      return reset($users);
    }
  }

  /**
   * {@inheritDoc}
   */
  function getAlias(UserInterface $user) : string {
    return strtolower($user->getEmail());
  }

  /**
   * {@inheritDoc}
   */
  function entityQuery(Condition $or_group, $match) {
    $or_group->condition('mail', $match, 'STARTS_WITH');
  }

}
