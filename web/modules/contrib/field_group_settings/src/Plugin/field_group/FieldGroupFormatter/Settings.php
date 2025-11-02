<?php

namespace Drupal\field_group_settings\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field_group\FieldGroupFormatterBase;
use Drupal\user\Entity\Role;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'settings' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "settings",
 *   label = @Translation("Settings"),
 *   description = @Translation("Renders a field group as collapsible settings."),
 *   supported_contexts = {
 *     "form",
 *   }
 * )
 */
class Settings extends FieldGroupFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $configuration['group'],
      $configuration['settings'],
      $configuration['label'],
    );

    $this->account = $current_user;
    $this->entityTypeManager = $entity_type_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object) {

    parent::preRender($element, $rendering_object);

    $element += [
      '#type' => 'field_group_settings',
      '#options' => [
        'attributes' => [
          'class' => $this->getClasses(),
        ],
      ],
      '#access' => $this->isVisible(),
    ];
  }

  /**
   * Get a list of role names.
   */
  protected function getRoleNames(): array {
    return array_map(function (Role $role) {
      return Html::escape($role->label());
    }, $this->entityTypeManager->getStorage('user_role')->loadMultiple());
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm() {
    $form = parent::settingsForm();

    $val = $this->getSetting('visible_for_roles');
    $roles = $this->getRoleNames();

    // See if any roles bypass the permission.
    $disabled = [];
    $role_objs = $this->entityTypeManager->getStorage('user_role')->loadMultiple(array_keys($roles));
    foreach ($role_objs as $role_id => $role) {
      if ($role->hasPermission('bypass field_group_settings field visibility')) {
        $disabled[$role_id] = $role_id;
        $val[$role_id] = $role_id;
      }
    }

    $form['visible_for_roles'] = [
      '#title' => $this->t('Roles that can view'),
      '#type' => 'checkboxes',
      '#options' => $roles,
      '#default_value' => $val,
      '#weight' => 2,
      '#description' => $this->t('Disabled options are managed by permissions.'),
    ];

    foreach ($disabled as $disabled_opt) {
      $form['visible_for_roles'][$disabled_opt] = [
        '#disabled' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $role_names = $this->getRoleNames();
    $visible = $this->getSetting('visible_for_roles');

    // Add global bypass settings.
    $role_objs = $this->entityTypeManager->getStorage('user_role')->loadMultiple(array_keys($role_names));
    foreach ($role_objs as $role_id => $role) {
      if ($role->hasPermission('bypass field_group_settings field visibility')) {
        $visible[$role_id] = 1;
      }
    }

    // Map allowed roles to their names.
    $allowed_role_names = array_map(function ($role_id) use ($role_names) {
      return $role_names[$role_id];
    }, array_keys(array_filter($visible)));

    $summary = [];
    if ($allowed_role_names) {
      $summary[] = $this->t('Visible for: @roles',
        ['@roles' => implode(', ', $allowed_role_names)]
      );
    }

    return $summary;
  }

  /**
   * Visibility check.
   */
  protected function isVisible() {
    $current_user = $this->account;
    if ($current_user->hasPermission('bypass field_group_settings field visibility')) {
      return TRUE;
    }
    $user_roles = $current_user->getRoles();
    $visible = $this->getSetting('visible_for_roles');
    if (empty($visible)) {
      return FALSE;
    }
    $allowed = array_filter($visible);
    if (empty($allowed)) {
      return FALSE;
    }
    $match = array_intersect($user_roles, $allowed);
    return (count($match) > 0);
  }

  /**
   * {@inheritdoc}
   */
  protected function getClasses() {
    $classes = ['field-group-settings'];
    $classes = array_merge($classes, parent::getClasses());
    return $classes;
  }

}
