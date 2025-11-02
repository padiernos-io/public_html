<?php

namespace Drupal\backstop_generator\Entity;

use Drupal\backstop_generator\BackstopJsonTemplate;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannel;

/**
 * Defines the backstop profile entity type.
 *
 * @ConfigEntityType(
 *   id = "backstop_profile",
 *   label = @Translation("Backstop Profile"),
 *   label_collection = @Translation("Backstop Profiles"),
 *   label_singular = @Translation("backstop profile"),
 *   label_plural = @Translation("backstop profiles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count backstop profile",
 *     plural = "@count backstop profiles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\backstop_generator\BackstopProfileListBuilder",
 *     "form" = {
 *       "add" = "Drupal\backstop_generator\Form\BackstopProfileForm",
 *       "edit" = "Drupal\backstop_generator\Form\BackstopProfileForm",
 *       "delete" = "Drupal\backstop_generator\Form\BackstopProfileDeleteForm",
 *       "express" = "Drupal\backstop_generator_express\Form\BackstopProfileExpressForm"
 *     },
 *     "storage" = "Drupal\backstop_generator\Entity\BackstopProfileStorage"
 *   },
 *   config_prefix = "profile",
 *   admin_permission = "administer backstop_generator",
 *   links = {
 *     "collection" = "/admin/config/development/backstop-generator/profiles",
 *     "add-form" = "/admin/config/development/backstop-generator/profile/add",
 *     "edit-form" = "/admin/config/development/backstop-generator/profile/{backstop_profile}",
 *     "delete-form" = "/admin/config/development/backstop-generator/profile/{backstop_profile}/delete",
 *     "express-form" = "/admin/config/development/backstop-generator/profile/express"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "test_domain",
 *     "reference_domain",
 *     "viewports",
 *     "onBeforeScript",
 *     "onReadyScript",
 *     "scenario_generator",
 *     "paths",
 *     "define_scenario_defaults",
 *     "scenarioDefaults",
 *     "scenario_list",
 *     "report",
 *     "engine",
 *     "engineOptions",
 *     "asyncCaptureLimit",
 *     "asyncCompareLimit",
 *     "debug",
 *     "debugWindow",
 *   }
 * )
 */
class BackstopProfile extends ConfigEntityBase {

  /**
   * The backstop profile ID.
   *
   * @var string
   *   The machine name of the profile.
   */
  protected $id;

  /**
   * The backstop profile label.
   *
   * @var string
   *   The human-readable name of the profile.
   */
  protected $label;

  /**
   * The backstop profile status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The backstop_profile description.
   *
   * @var string
   *   A description of the profile.
   */
  protected $description;

  /**
   * The test domain for the profile.
   *
   * @var string
   */
  protected $test_domain;

  /**
   * The reference domain for the profile.
   *
   * @var string
   */
  protected $reference_domain;

  /**
   * List of viewports included in this profile.
   *
   * @var array
   *   An array of viewport configurations including height and width.
   */
  protected $viewports;

  /**
   * Determines whether to run the onBeforeScript.
   *
   * @var bool
   */
  protected $onBeforeScript;

  /**
   * Determines whether to run the onReadyScript.
   *
   * @var bool
   */
  protected $onReadyScript;

  /**
   * The list of scenarios (pages) in this profile.
   *
   * @var array
   *   An array of scenario IDs.
   */
  protected $scenarios;

  /**
   * The paths where test results will be saved.
   *
   * @var string
   *   Options: 'bitmaps_reference', 'bitmaps_test', 'html_report', 'ci_report'.
   */
  protected $paths;

  /**
   * The default values for scenarios in this profile.
   *
   * @var array
   *   An array of scenario default field values.
   */
  protected $scenarioDefaults;

  /**
   * The list of scenario IDs.
   *
   * @var array
   *   An array of scenario IDs.
   */
  protected $scenario_list;

  /**
   * The name of the engine used to run the tests.
   *
   * @var string
   *   Options: 'puppeteer', 'playwright'.
   */
  protected $engine;

  /**
   * A list of Chromium flags to send to the selected testing engine.
   *
   * @var string
   *   Options: 'headless'|'no-sandbox'|'disable-gpu'|'disable-dev-shm-usage'
   */
  protected $engineOptions;

  /**
   * The number of async captures to run at once.
   *
   * @var int
   */
  protected $asyncCaptureLimit;

  /**
   * The number of async compares to run at once.
   *
   * @var int
   */
  protected $asyncCompareLimit;

  /**
   * Determines whether to run the tests in debug mode.
   *
   * @var bool
   */
  protected $debug;

  /**
   * Determines whether to run the tests in debug mode with a window.
   *
   * @var bool
   */
  protected $debugWindow;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|null
   */
  protected $configFactory;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface|null
   */
  protected $fileSystem;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannel|null
   */
  protected $logger;

  /**
   * Constructs a new BackstopProfile object.
   *
   * @param array $values
   *   An array of values.
   * @param string $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface|null $configFactory
   *   The config factory service.
   * @param \Drupal\Core\File\FileSystemInterface|null $fileSystem
   *   The file system service.
   * @param \Drupal\Core\Logger\LoggerChannel|null $logger
   *   The logger service.
   */
  public function __construct(
    array $values,
    $entity_type,
    EntityTypeManagerInterface|null $entity_type_manager = NULL,
    ConfigFactoryInterface|null $configFactory = NULL,
    FileSystemInterface|null $fileSystem = NULL,
    LoggerChannel|null $logger = NULL,
  ) {
    parent::__construct($values, $entity_type);
    $this->entityTypeManager = $entity_type_manager ?? \Drupal::entityTypeManager();
    $this->configFactory = $configFactory ?? \Drupal::configFactory();
    $this->fileSystem = $fileSystem ?? \Drupal::service('file_system');
    $this->logger = $logger ?? \Drupal::service('logger.channel.backstop_generator');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(array $values = []) {
    $entity_type = 'backstop_profile';
    $container = \Drupal::getContainer();
    return new static(
      $values,
      $entity_type,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('logger.channel.backstop_generator')
    );
  }

  /**
   * Generates the JSON file for this profile.
   */
  public function generateBackstopFile() {
    // Create the JSON template object and set the field values.
    $profile = new BackstopJsonTemplate($this->id());
    $profile->set('viewports', $this->getViewports());
    $profile->set('scenarioDefaults', $this->getScenarioDefaults());
    $profile->set('scenarios', $this->getScenarios());
    $profile->set('engine', $this->engine);
    $profile->set('engineOptions', $this->getEngineOptions());
    $profile->set('asyncCaptureLimit', $this->asyncCaptureLimit);
    $profile->set('asyncCompareLimit', $this->asyncCompareLimit);
    $profile->set('debug', $this->debug);
    $profile->set('debugWindow', $this->debugWindow);
    if ($this->onBeforeScript) {
      $profile->set('onBeforeScript', $this->getScript('onBeforeScript'));
    }
    if ($this->onReadyScript) {
      $profile->set('onReadyScript', $this->getScript('onReadyScript'));
    }

    // Prepare the backstop directory.
    $backstop_directory = $this->prepareBackstopDirectory();
    $filename = $this->id == 'backstop' ? 'backstop.json' : "bsg_$this->id.json";

    // Create, write and save the backstop JSON file.
    $backstop_file = fopen("$backstop_directory/$filename", "w");
    fwrite($backstop_file, $this->getJson($profile));
    fclose($backstop_file);
  }

  /**
   * Return the viewports array.
   *
   * @return array
   *   An array of configured viewports.
   */
  private function getViewports() {
    $viewports = [];
    foreach (array_filter($this->viewports) as $viewport_id) {
      $viewport = BackstopViewport::load($viewport_id);
      $viewport_json = $viewport->getJsonKeyValues();
      $viewports[] = $viewport_json;
    }
    return $viewports;
  }

  /**
   * Returns the scenario defaults array.
   *
   * @return array
   *   An array of configured scenario default settings.
   */
  private function getScenarioDefaults() {
    return backstop_generator_process_scenario_values($this->scenarioDefaults, $this->id);
  }

  /**
   * Returns the array of selected scenario options.
   *
   * @return array
   *   An array containing the configured scenario settings.
   */
  private function getScenarios() {
    $scenarios = [];
    foreach (array_filter($this->scenario_list) as $scenario_id) {
      $scenario = BackstopScenario::load($scenario_id);
      $scenario_json = $scenario->getJsonKeyValues();
      $scenarios[] = $scenario_json;
    }
    return $scenarios;
  }

  /**
   * Prepares the backstop directory for the JSON file.
   *
   * @return false|string
   *   The full path to the backstop directory as configured
   *   in the module settings.
   */
  private function prepareBackstopDirectory() {
    try {
      // Get the directory from config.
      $backstop_directory_path = $this->configFactory
        ->get('backstop_generator.settings')
        ->get('backstop_directory');

      // Construct the full path.
      $project_root = dirname(DRUPAL_ROOT);
      $backstop_directory = "$project_root/{$backstop_directory_path}";

      // Create the backstop directory (or verify it exists).
      if ($this->fileSystem->prepareDirectory(
        $backstop_directory,
        FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS
        )
      ) {
        return $backstop_directory;
      }
      else {
        throw new \Exception('Failed to prepare the backstop directory.');
      }

    }
    catch (\Exception $e) {
      // Log the error.
      $this->logger->error('The directory could not be created: %error', ['%error' => $e->getMessage()]);
      return FALSE;
    }
  }

  /**
   * Prepares the paths field value for the JSON file.
   *
   * @return array
   *   An array of paths where the test results will be saved.
   */
  private function preparePaths() {
    $paths = [];
    $path_field_value = array_map('trim', explode("\r\n", $this->paths));
    foreach ($path_field_value as $path) {
      preg_match('/^([\w]+)\|([\w]+)/', $path, $matches);
      if (!empty($matches)) {
        $paths[$matches[1]] = $matches[2];
      }
    }
    return $paths;
  }

  /**
   * Returns the JSON representation of the profile.
   *
   * @param \Drupal\backstop_generator\BackstopJsonTemplate $profile
   *   The profile object.
   *
   * @return false|string
   *   The JSON representation of the profile.
   */
  private function getJson(BackstopJsonTemplate $profile) {
    return json_encode(
      $profile,
      JSON_PRETTY_PRINT |
      JSON_UNESCAPED_SLASHES |
      JSON_UNESCAPED_UNICODE |
      JSON_NUMERIC_CHECK
    );
  }

  /**
   * Returns the path to the script file.
   *
   * @param string $field_name
   *   The field name which currently serves as the file name.
   *
   * @return string|bool
   *   The path to the script file or FALSE.
   */
  private function getScript(string $field_name): string|bool {
    if ($this->$field_name) {
      return "{$this->engine}/$field_name.js";
    }
    return FALSE;
  }

  /**
   * Returns the engine options array.
   *
   * @return array
   *   An associative array of engine options arguments.
   */
  private function getEngineOptions() {
    return [
      'args' => array_map('trim', explode(',', $this->engineOptions)),
    ];
  }

}
