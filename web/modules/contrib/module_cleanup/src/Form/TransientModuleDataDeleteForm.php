<?php

namespace Drupal\module_cleanup\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Update\UpdateHookRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes transient data.
 *
 * @package Drupal\module_cleanup\Form
 */
class TransientModuleDataDeleteForm extends FormBase {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The update hook registery.
   *
   * @var \Drupal\Core\Update\UpdateHookRegistry
   */
  protected $updateHookRegistry;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * TransientModuleDataDeleteForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Update\UpdateHookRegistry $updateHookRegistry
   *   Versioning update registry service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   */
  public function __construct(
    MessengerInterface $messenger,
    Connection $database,
    UpdateHookRegistry $updateHookRegistry,
    ModuleExtensionList $moduleExtensionList,
  ) {
    $this->messenger = $messenger;
    $this->database = $database;
    $this->updateHookRegistry = $updateHookRegistry;
    $this->moduleExtensionList = $moduleExtensionList;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database'),
      $container->get('update.update_hook_registry'),
      $container->get('extension.list.module')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_cleanup_transient_data_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $modules = $this->updateHookRegistry->getAllInstalledVersions();
    $installed_module_info = $this->moduleExtensionList->getAllInstalledInfo();
    $options = [];
    foreach ($modules as $module => $schema_version) {
      if (empty($installed_module_info[$module])) {
        $options[$module] = $this->createName($module);
      }
    }

    $attributes = [];
    if (count($options) == 0) {
      $attributes = [
        'disabled' => 'disabled',
      ];
    }

    $form['module_data'] = [
      '#type' => 'details',
      '#title' => $this->t('Transient Module Data'),
      '#description' => $this->t('This is a fix for Module "module_name" has an entry in the system.schema key/value storage. Modules will only show up if there is data to erase.'),
      '#open' => TRUE,
    ];

    $form['module_data']['modules'] = [
      '#type' => 'checkboxes',
      '#required' => TRUE,
      '#title' => $this->t('Select the module to delete leftover transient data.'),
      '#options' => $options,
      '#default_value' => array_keys($options),
    ];

    $form['module_data']['actions'] = ['#type' => 'actions'];
    $form['module_data']['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Delete transient module data.'),
      '#attributes' => $attributes,
    ];

    return $form;
  }

  /**
   * Create a capitalizes name from machine name.
   *
   * @param string $machine_name
   *   The machine name.
   */
  private function createName($machine_name) {
    return ucfirst(implode(" ", explode("_", $machine_name)));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasValue('modules')) {
      foreach ($form_state->getValue('modules') as $module) {
        $this->database->delete('key_value')->condition('name', $module)->execute();
        $this->messenger->addMessage($this->t("%module transient data deleted.", ['%module' => $this->createName($module)]));
      }
    }
    else {
      $this->messenger->addMessage("No modules selected.");
    }
  }

}
