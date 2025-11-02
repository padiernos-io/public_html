<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopProfile;
use Drupal\backstop_generator\Services\BackstopFormBuilder;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Global Backstop settings.
 */
class SettingsForm extends ConfigFormBase {
  use StringTranslationTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The BackstopFormBuilder service.
   *
   * @var \Drupal\backstop_generator\Services\BackstopFormBuilder
   */
  protected BackstopFormBuilder $backstopFormBuilder;

  /**
   * The map of config settings used when saving this form.
   *
   * @var array
   */
  protected array $configMap = [
    'backstop_directory' => 'backstop_directory',
    'test_domain' => 'test_domain',
    'reference_domain' => 'reference_domain',
    'scenarioDefaults' => [
      'delay',
      'hideSelectors',
      'removeSelectors',
      'misMatchThreshold',
      'requireSameDimensions',
      'cookiePath',
      'readyEvent',
      'readySelector',
      'readyTimeout',
      'onReadyScript',
      'onBeforeScript',
      'keyPressSelectors',
      'hoverSelector',
      'hoverSelectors',
      'clickSelector',
      'clickSelectors',
      'postInteractionWait',
      'scrollToSelector',
      'selectors',
      'selectorExpansion',
      'expect',
      'gotoParameters',
    ],
    'profile_parameters' => [
      'onBeforeScript',
      'onReadyScript',
      'paths',
      'report',
      'asyncCaptureLimit',
      'asyncCompareLimit',
      'engine',
      'engineOptions',
    ],
  ];

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\backstop_generator\Services\BackstopFormBuilder $backstopFormBuilder
   *   The BackstopFormBuilder service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager service.
   */
  public function __construct(
    FileSystemInterface $fileSystem,
    EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface $configFactory,
    ModuleHandlerInterface $moduleHandler,
    BackstopFormBuilder $backstopFormBuilder,
    TypedConfigManagerInterface $typedConfigManager,
  ) {
    parent::__construct($configFactory, $typedConfigManager);
    $this->fileSystem = $fileSystem;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->backstopFormBuilder = $backstopFormBuilder;
    $this->typedConfigManager = $typedConfigManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('backstop_generator.form_builder'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'backstop_generator_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['backstop_generator.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('backstop_generator.settings');

    // Attach the backstop_forms libraries.
    $form['#attached']['library'][] = 'backstop_generator/add-path-handler';
    $server = $_SERVER;
    $site = "{$server['REQUEST_SCHEME']}://{$server['SERVER_NAME']}";

    $form['backstop_directory'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Backstop Directory'),
      '#description' => $this->t('This directory is in the <em>project</em> root, one level above your Drupal site.'),
      '#description_display' => 'before',
      '#default_value' => $config->get('backstop_directory') ?? '/tests/backstop',
      '#attributes' => [
        'disabled' => 'disabled',
      ],
    ];

    $settings = $this->config('backstop_generator.settings');
    $form = $this->backstopFormBuilder->buildUrlSection($form);

    $profile_defaults = $this->config('backstop_generator.settings')->get('profile_parameters') ?? [];
    $form = $this->backstopFormBuilder->buildProfileParametersSection($form, $profile_defaults, TRUE);

    $scenario_defaults = $this->config('backstop_generator.settings')->get('scenarioDefaults') ?? [];
    $form = $this->backstopFormBuilder->buildScenarioDefaultsSection($form, $scenario_defaults);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Create the backstop directory.
    $this->createBackstopDirectory($form_state);

    // Save the form values to config.
    $config = $this->config('backstop_generator.settings');
    foreach ($this->configMap as $config_key => $config_val) {
      // Handle parent groups.
      if (is_array($config_val)) {
        foreach ($config_val as $config_name) {
          $config->set("$config_key.$config_name", $form_state->getValue([$config_key, $config_name]));
        }
      }
      // Handle root level field values.
      else {
        $config->set($config_val, $form_state->getValue($config_val));
      }
    }
    $config->save();
    parent::submitForm($form, $form_state);

    // Update the backstop.json files for profiles that use global settings.
    $updated_profiles = $this->updateProfiles();

    // Inform the user which backstop.json files have been updated.
    $count = count($updated_profiles);
    $message = $this->formatPlural(
      $count,
      'The %label backstop.json file has been updated.',
      'The %label backstop.json files have been updated.',
      ['%label' => implode(', ', $updated_profiles)]
    );

    if ($count === 0) {
      $message = $this->t('No backstop.json files needed to be updated.');
    }

    $this->messenger()->addMessage($message);
  }

  /**
   * Creates the backstop directory.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return void
   *   No return value. The function creates the directory.
   */
  private function createBackstopDirectory(FormStateInterface $form_state) {
    // If a new directory is created, delete the old directory.
    $current_dir = $this->config('backstop_generator.settings')
      ->get('backstop_directory');

    $new_dir = $form_state->getValue('backstop_directory');

    if ($current_dir && $current_dir != $new_dir) {
      // Create the backstop directory where profiles will be saved.
      $project_root = dirname(DRUPAL_ROOT);
      $profile_directory = "$project_root/{$form_state->getValue('backstop_directory')}";

      $this->fileSystem->prepareDirectory(
        $profile_directory,
        FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
      );
    }
  }

  /**
   * Regenerate the backstop.json files for Profiles that use global settings.
   *
   * @return array
   *   Array of profile names that were updated.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function updateProfiles() {
    $profile_ids = $this->configFactory->listAll('backstop_generator.profile');
    $settings = $this->config('backstop_generator.settings');
    $regenerated_profiles = [];

    if (!empty($profile_ids)) {
      foreach ($profile_ids as $id) {
        // Load the profile config.
        $profile_config = $this->configFactory()->getEditable($id);

        // Ignore profiles using custom settings.
        if (!$profile_config->get('useProfileDefaults')) {
          continue;
        }

        // Update the config values and save.
        $profile_config->set('onBeforeScript', $settings->get('onBeforeScript'))
          ->set('paths', $settings->get('paths'))
          ->set('report', $settings->get('report'))
          ->set('engine', $settings->get('engine'))
          ->set('engineOptions', $settings->get('engineOptions'))
          ->set('asyncCaptureLimit', $settings->get('asyncCaptureLimit'))
          ->set('asyncCompareLimit', $settings->get('asyncCompareLimit'));
        $profile_config->save();

        // Regenerate the backstop.json file.
        // Remove the 'backstop_generator.profile.' prefix.
        $pid = substr($id, 27);
        $profile = BackstopProfile::load($pid);
        $profile->generateBackstopFile($pid);
        $regenerated_profiles[] = $profile->label();
      }
    }
    return $regenerated_profiles;
  }

  /**
   * Returns an array of config labels.
   *
   * @param string $config_name
   *   The name of the configuration entity (ex. backstop_viewport).
   *
   * @return array
   *   The list of config labels keyed by the config ID.
   */
  protected function getConfig(string $config_name) {
    // Get the config entity manager.
    $entity_storage = $this->entityTypeManager
      ->getStorage($config_name);
    // Get the entity query object.
    $entity_query = $entity_storage->getQuery();
    $entity_query->accessCheck();
    $config_ids = $entity_query->execute();
    // Load the config entities.
    $configs = $entity_storage->loadMultiple($config_ids);

    // Create the array of configs.
    $config_list = [];
    foreach ($configs as $config) {
      if ($config_name == 'backstop_scenario') {
        $config_list[$config->id()] = ucfirst($config->get('bundle')) . ": {$config->label()}";
        continue;
      }
      $config_list[$config->id()] = $config->label();
    }
    asort($config_list);
    return $config_list;
  }

  /**
   * Checks if a module is installed.
   *
   * @param string $module_machine_name
   *   The machine name of the module.
   *
   * @return bool
   *   Flags TRUE if the module is installed, FALSE otherwise.
   */
  public function moduleExists(string $module_machine_name): bool {
    return $this->moduleHandler->moduleExists($module_machine_name);
  }

}
