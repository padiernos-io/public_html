<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopScenario;
use Drupal\backstop_generator\Services\BackstopFormBuilder;
use Drupal\backstop_generator\Services\MenuNodeData;
use Drupal\backstop_generator\Services\RandomNodeList;
use Drupal\backstop_generator\Services\ScenarioGenerator;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\path_alias\AliasManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Backstop Profile form.
 */
class BackstopProfileForm extends EntityForm {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The random node list service.
   *
   * @var \Drupal\backstop_generator\Services\RandomNodeList
   */
  protected $randomNodeList;

  /**
   * The menu node data service.
   *
   * @var \Drupal\backstop_generator\Services\MenuNodeData
   */
  protected $menuNodeData;

  /**
   * The path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $pathAliasManager;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The scenario generator service.
   *
   * @var \Drupal\backstop_generator\Services\ScenarioGenerator
   */
  protected $scenarioGenerator;

  /**
   * The backstop form builder service.
   *
   * @var \Drupal\backstop_generator\Services\BackstopFormBuilder
   */
  protected $backstopFormBuilder;

  /**
   * Constructs a new BackstopProfileForm object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   * @param \Drupal\backstop_generator\Services\RandomNodeList $random_node_list
   *   The random node list service.
   * @param \Drupal\backstop_generator\Services\MenuNodeData $menu_node_data
   *   The menu node data service.
   * @param \Drupal\path_alias\AliasManager $path_alias_manager
   *   The path alias manager service.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger service.
   * @param \Drupal\backstop_generator\Services\ScenarioGenerator $scenario_generator
   *   The scenario generator service.
   * @param \Drupal\backstop_generator\Services\BackstopFormBuilder $backstopFormBuilder
   *   The backstop form builder service.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactory $config_factory,
    RandomNodeList $random_node_list,
    MenuNodeData $menu_node_data,
    AliasManager $path_alias_manager,
    LoggerChannelInterface $logger,
    ScenarioGenerator $scenario_generator,
    BackstopFormBuilder $backstopFormBuilder,
  ) {
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->randomNodeList = $random_node_list;
    $this->menuNodeData = $menu_node_data;
    $this->pathAliasManager = $path_alias_manager;
    $this->logger = $logger;
    $this->scenarioGenerator = $scenario_generator;
    $this->backstopFormBuilder = $backstopFormBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('backstop_generator.random_node_list'),
      $container->get('backstop_generator.menu_node_data'),
      $container->get('path_alias.manager'),
      $container->get('logger.channel.backstop_generator'),
      $container->get('backstop_generator.scenario_generator'),
      $container->get('backstop_generator.form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $viewport_url = Url::fromRoute('entity.backstop_viewport.add_form');
    $viewport_link = Link::fromTextAndUrl($this->t('Add a Viewport'), $viewport_url);
    $backstop_config = $this->configFactory->get('backstop_generator.settings');
    $default_language = $this->languageManager->getDefaultLanguage();
    $defaults = $this->entity->toArray() ?? [];
    $server = $_SERVER;
    $site = "{$server['REQUEST_SCHEME']}://{$server['SERVER_NAME']}";

    $form = parent::form($form, $form_state);

    // Attach the backstop_forms libraries.
    $form['#attached']['library'][] = 'backstop_generator/profile_form';

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t("Label for this profile.<br>NOTE: For your first profile, use the name 'Backstop' to create a file called 'backstop.json'."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\backstop_generator\Entity\BackstopProfile::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->get('description'),
      '#description' => $this->t('A brief description of what this test covers.'),
      '#description_display' => 'before',
      '#attributes' => [
        'rows' => 3,
      ],
    ];

    $form = $this->backstopFormBuilder->buildUrlSection($form, $defaults);

    $form['show_debug_settings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show debug settings'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="id"]' => ['!value' => ''],
        ],
      ],
    ];

    $form['debugging'] = [
      '#type' => 'details',
      '#title' => $this->t('Debugging'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="show_debug_settings"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['debugging']['debug'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('debug'),
      '#description' => $this->backstopFormBuilder->getFieldDescription('debug'),
      '#description_display' => 'before',
      '#default_value' => $this->entity->get('debug') ?? $backstop_config->get('debug'),
      '#attributes' => [
        'readonly' => $this->entity->get('use_globals') ?? TRUE,
        'class' => ['advanced-setting'],
      ],
    ];

    $form['debugging']['debugWindow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('debugWindow'),
      '#description' => $this->backstopFormBuilder->getFieldDescription('debugWindow'),
      '#description_display' => 'before',
      '#default_value' => $this->entity->get('debugWindow') ?? $backstop_config->get('debugWindow'),
      '#attributes' => [
        'readonly' => $this->entity->get('use_globals') ?? TRUE,
        'class' => ['advanced-setting'],
      ],
    ];

    $form['head_hr'] = [
      '#type' => 'markup',
      '#markup' => '<hr>',
    ];

    $form['viewports'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Viewports'),
      '#description' => $this->t('Select the viewports to include in this profile'),
      '#description_display' => 'before',
      '#options' => $this->getConfig('backstop_viewport'),
      '#default_value' => $this->entity->get('viewports') ?? [],
      '#suffix' => $viewport_link->toString()->getGeneratedLink(),
      '#required' => TRUE,
    ];

    $scenario_defaults = $defaults['scenarioDefaults'] ?? $backstop_config->get('scenario_defaults') ?? [];
    $form = $this->backstopFormBuilder->buildProfileParametersSection($form, $defaults);
    $form = $this->backstopFormBuilder->buildScenarioDefaultsSection($form, $scenario_defaults);

    $form['scenario_generator_top_hr'] = [
      '#type' => 'markup',
      '#markup' => '<hr>',
    ];

    $form['overwrite_scenarios'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Overwrite scenarios'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="id"]' => ['!value' => ''],
        ],
      ],
      '#description' => $this->t('Generate new scenarios for this profile.'),
    ];

    $generatorSettings = $this->entity->get('scenario_generator');

    $form['scenario_generator'] = [
      '#type' => 'details',
      '#title' => $this->t('Scenario Generator'),
      '#open' => TRUE,
      '#description' => $this->t('Generate scenarios for this profile using the options below.'),
      '#states' => [
        'visible' => $this->entity->isNew() ? [] : [
          ':input[name="overwrite_scenarios"]' => ['checked' => TRUE],
        ],
      ],
      'language_list' => [
        '#type' => 'checkboxes',
        '#title' => $this->t('Available Languages'),
        '#options' => $this->getLanguageOptions(),
        '#default_value' => $this->entity->get('scenario_generator')['language_list'] ?? [$default_language->getId()],
        '#description' => $this->t('Select languages to include translations of these nodes.'),
        '#required' => TRUE,
        '#parents' => [
          'scenario_generator',
          'language_list',
        ],
      ],
      'include_homepage' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Homepage'),
        '#default_value' => $this->entity->get('scenario_generator')['include_homepage'] ?? TRUE,
        '#parents' => [
          'scenario_generator',
          'include_homepage',
        ],
        '#description' => $this->t('Check this box to include the homepage as a scenario.'),
      ],
      'menus' => [
        '#type' => 'details',
        '#title' => $this->t('Scenarios From Menu Items'),
        '#description' => $this->t('Create scenarios from menu links.'),
        '#open' => TRUE,
      ],
      'content_types' => [
        '#type' => 'details',
        '#title' => $this->t('Scenarios From Content Types'),
        '#description' => $this->t('Create scenarios of randomly selected content from content types.'),
        '#open' => FALSE,
      ],
      'paths' => [
        '#type' => 'details',
        '#title' => $this->t('Scenarios From Nodes and Paths'),
        '#description' => $this->t('Create scenarios from Nodes and specific paths (like Views pages).'),
        '#open' => FALSE,
      ],
    ];

    $form['scenario_generator']['menus']['menu_list'] = [
      '#type' => 'checkboxes',
      '#title' => 'Menus',
      '#description' => $this->t('Select which menus to include.'),
      '#description_display' => 'before',
      '#options' => $this->getAllMenus(),
      '#default_value' => $generatorSettings['menu_list'] ?? ['main'],
      '#parents' => [
        'scenario_generator',
        'menu_list',
      ],
    ];

    $form['scenario_generator']['menus']['menu_depth'] = [
      '#type' => 'number',
      '#title' => $this->t('Menu depth'),
      '#description' => $this->t('Select the maximum depth of menu links to test - select 20 for all items.'),
      '#description_display' => 'before',
      '#required' => FALSE,
      '#min' => 0,
      '#max' => 20,
      '#step' => 1,
      '#default_value' => $generatorSettings['menu_depth'] ?? 2,
      '#parents' => [
        'scenario_generator',
        'menu_depth',
      ],
    ];

    $form['scenario_generator']['content_types']['node_quantity'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of each content type to test'),
      '#description' => $this->t('Enter the number of scenarios to create for each content type.'),
      '#description_display' => 'before',
      '#required' => FALSE,
      '#min' => 0,
      '#max' => 20,
      '#step' => 1,
      '#default_value' => $generatorSettings['node_quantity'] ?? 5,
      '#parents' => [
        'scenario_generator',
        'node_quantity',
      ],
    ];

    $form['scenario_generator']['content_types']['content_type_list'] = [
      '#type' => 'checkboxes',
      '#title' => 'Node Types',
      '#description' => $this->t('Select which node types to include.'),
      '#description_display' => 'before',
      '#options' => $this->getAllContentTypes(),
      '#default_value' => $generatorSettings['content_type_list'] ?? [],
      '#parents' => [
        'scenario_generator',
        'content_type_list',
      ],
    ];

    $form['scenario_generator']['paths']['node_title'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Node lookup'),
      '#description' => $this->t("Enter a node title, then click the 'Add path' button to add it to the list of paths below."),
      '#description_display' => 'before',
      '#target_type' => 'node',
      '#attributes' => ['id' => 'node-title'],
    ];

    $form['scenario_generator']['paths']['add_button'] = [
      '#type' => 'button',
      '#value' => $this->t('Add path'),
      '#attributes' => ['id' => 'add-path-to-list'],
    ];

    $form['scenario_generator']['paths']['path_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Path list'),
      '#prefix' => '<div id="path-list-wrapper">',
      '#suffix' => '</div>',
      '#default_value' => $generatorSettings['path_list'] ?? '',
      '#parents' => [
        'scenario_generator',
        'path_list',
      ],
      '#description' => $this->t('Add specific paths to test with labels separated by a pipe. (ex. label | path). One entry per line.'),
      '#description_display' => 'before',
    ];

    $form['scenario_generator']['generate_scenarios'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Scenarios'),
      '#attributes' => ['id' => 'generate-scenarios-button'],
      '#submit' => ['::generateNewScenarios'],
      '#states' => [
        'visible' => [
          ':input[name="id"]' => ['!value' => ''],
        ],
      ],
    ];

    $form['scenario_generator_bottom_hr'] = [
      '#type' => 'markup',
      '#markup' => '<hr>',
    ];

    if (!$this->entity->isNew()) {
      $scenario_options = $this->getConfig('backstop_scenario') ?? [];
      $scenario_count = count($scenario_options);

      $form['select_all'] = [
        '#type' => 'checkbox',
        '#title' => $this->t(
          'Select all (@count total)', ['@count' => $scenario_count]),
        '#attributes' => [
          'id' => 'select-all',
          'name' => 'select_all',
        ],
        '#states' => [
          'visible' => $this->entity->isNew() ? [] : [
            ':input[name="overwrite_scenarios"]' => ['checked' => FALSE],
          ],
        ],
        '#description' => $this->t('Check/uncheck to select or deselect all scenarios below.'),
      ];

      $form['scenario_list'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t(
          'Scenarios (@count)', ['@count' => $scenario_count]),
        '#description' => $this->t('Select the scenarios to include in this profile.'),
        '#description_display' => 'before',
        '#options' => $this->getConfig('backstop_scenario') ?? [],
        '#default_value' => ($this->entity->get('scenario_list')) ?? [],
        '#prefix' => '<div id="scenario-list-wrapper">',
        '#suffix' => '</div>',
        '#states' => [
          'visible' => $this->entity->isNew() ? [] : [
            ':input[name="overwrite_scenarios"]' => ['checked' => FALSE],
          ],
        ],
      ];

      $form['remove_unselected'] = [
        '#type' => 'fieldset',
        '#description' => $this->t("Press the 'D' key when clicking the 'Delete unselected' button to remove unselected scenarios."),
        '#description_display' => 'before',
        '#states' => [
          'visible' => $this->entity->isNew() ? [] : [
            ':input[name="overwrite_scenarios"]' => ['checked' => FALSE],
          ],
        ],
        'delete_unselected_scenarios' => [
          '#type' => 'submit',
          '#value' => $this->t('Delete Unselected'),
          '#description' => $this->t("Hold the 'D' key when clicking this button."),
          '#attributes' => ['id' => 'delete-unselected-scenarios-button'],
          '#submit' => ['::removeUncheckedScenarios'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Ensure the overwrite_scenario switch is always false.
    $form['overwrite_scenarios']['#value'] = FALSE;

    // Modify the save button text for new profiles.
    if ($this->entity->isNew()) {
      $form['actions']['submit']['#value'] = $this->t('Save and generate scenarios');
    }
    else {
      $form['actions']['submit']['#value'] = $this->t('Save');
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    if ($this->entity->isNew() || $form_state->getValue(['scenario_generator', 'overwrite_scenarios'])) {
      // Reload the form after generating scenarios.
      $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
      $result = parent::save($form, $form_state);
      $this->generateNewScenarios($form, $form_state, FALSE);
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
      $result = parent::save($form, $form_state);

      // Update the scenarios if the test or reference domain has changed.
      if (
        $form['test_domain'] != $form_state->getValue('test_domain') ||
        $form['reference_domain'] != $form_state->getValue('reference_domain')
      ) {
        $this->updateScenarios($form_state);
      }
      // Generate the backstop file if the profile is not new.
      $this->entity->generateBackstopFile();
    }

    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new backstop profile %label.', $message_args)
      : $this->t('Updated backstop profile %label.', $message_args);
    $this->messenger()->addStatus($message);

    return $result;
  }

  /**
   * Returns a list of configuration entity names keyed by their ID.
   *
   * This method returns the list of configured Viewports/Scenarios.
   *
   * @param string $config_name
   *   The name of the configuration entity to retrieve.
   *
   * @return array
   *   An array of configuration entity names keyed by their ID.
   */
  protected function getConfig(string $config_name) {
    $config_list = [];
    switch ($config_name) {
      case 'backstop_viewport':
        $configs = $this->configFactory->listAll('backstop_generator.viewport.');
        foreach ($configs as $cid) {
          $config = $this->configFactory->get($cid);
          $config_list[$config->get('id')] = $config->get('label');
        }
        break;

      case 'backstop_scenario':
        // Get a list of all backstop_scenarios that have
        // $this->entity->id() in the name.
        $configs = $this->configFactory->listAll(
          'backstop_generator.scenario.' . $this->entity->id() . '_'
        );
        foreach ($configs as $cid) {
          $config = $this->configFactory->get($cid);
          $bundle = $config->get('bundle') ?? '';
          $config_list[$config->get('id')] = ucfirst($bundle) . ": {$config->get('label')}";
        }
        break;
    }
    asort($config_list);
    return $config_list;
  }

  /**
   * Get all menus in the system.
   *
   * Used by Scenario Generator to create scenarios from menu links.
   *
   * @return array
   *   An array of all menus.
   */
  public function getAllMenus(): array {
    $menu_storage = $this->entityTypeManager->getStorage('menu');
    $menus = $menu_storage->loadMultiple();
    $menu_list = [];

    foreach ($menus as $menu) {
      $menu_list[$menu->id()] = $menu->label();
    }

    return $menu_list;
  }

  /**
   * Get all content types in the system.
   *
   * Used by Scenario Generator to create scenarios from content types.
   *
   * @return array
   *   An array of all content types.
   */
  private function getAllContentTypes(): array {
    $content_types = [];
    $node_type_storage = $this->entityTypeManager->getStorage('node_type');
    $types = $node_type_storage->loadMultiple();

    foreach ($types as $type) {
      $content_types[$type->id()] = $type->label();
    }

    return $content_types;
  }

  /**
   * Returns the available language options.
   *
   * Used by the Scenario Generator to create scenarios from translated content.
   *
   * @return array
   *   An array of language options.
   */
  protected function getLanguageOptions() {
    $language_options = [];
    $languages = $this->languageManager->getLanguages();
    $default_language = $this->languageManager->getDefaultLanguage()->getId();

    foreach ($languages as $language) {
      if ($language->getId() === $default_language) {
        $language_options[$language->getId()] = "{$language->getName()} (default)";
        continue;
      }
      $language_options[$language->getId()] = $language->getName();
    }
    return $language_options;
  }

  /**
   * AJAX callback: Remove unchecked scenarios from the profile.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function removeUncheckedScenarios(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $selected = array_filter($values['scenario_list']);
    $unselected = array_diff_key($values['scenario_list'], $selected);

    foreach ($unselected as $id => $value) {
      $config = $this->configFactory->getEditable("backstop_generator.scenario.$id");
      $config->delete();
    }

    $this->messenger()->addStatus($this->t('Removed unchecked scenarios.'));
    $form_state->setRebuild();
  }

  /**
   * Returns the basic properties for a scenario.
   *
   * @return array
   *   An array of basic scenario properties.
   */
  public function getBasicScenarioProperties() {
    $entity = $this->entity->toArray();
    return [
      'label' => '',
      'url' => $entity['test_domain'] ?? $this->config('backstop_generator.settings')->get('test_domain'),
      'referenceUrl' => $entity['reference_domain'] ?? $this->config('backstop_generator.settings')->get('reference_domain'),
      'useScenarioDefaults' => TRUE,
      'profile_id' => $this->entity->id(),
    ];
  }

  /**
   * Submission handler for the 'Generate Scenarios' button on the Profile form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param bool $rebuildForm
   *   Whether to rebuild the form after generating scenarios.
   *
   * @return void
   *   The form state is modified to include the generated scenarios.
   */
  public function generateNewScenarios(array $form, FormStateInterface $form_state, $rebuildForm = TRUE) {
    $values = $form_state->getValues();
    $entity_id = $values['id'];
    $default_lang_id = $this->languageManager->getDefaultLanguage()->getId();
    $properties = $this->getBasicScenarioProperties();

    $language_data = [
      'language_list' => array_filter($values['scenario_generator']['language_list']) ?? [$default_lang_id],
      'default_language' => $default_lang_id,
    ];

    $menu_data = [
      'menu_list' => array_filter($values['scenario_generator']['menu_list']),
      'menu_depth' => $values['scenario_generator']['menu_depth'],
    ];

    $content_type_data = [
      'content_type_list' => array_filter($values['scenario_generator']['content_type_list']),
      'node_quantity' => $values['scenario_generator']['node_quantity'],
    ];

    $path_data = explode("\r\n", $values['scenario_generator']['path_list']);

    // Delete this profile's existing scenarios.
    $this->scenarioGenerator->removeScenarios($entity_id);

    // Generate the new scenarios.
    // From the home page.
    $homepage = $form_state->getValue(['scenario_generator', 'include_homepage']);
    if ($homepage) {
      $this->scenarioGenerator->scenariosFromHomepage($properties, $language_data);
    }

    // From menus.
    $this->scenarioGenerator->scenariosFromMenus($properties, $language_data, $menu_data);

    // From content types.
    $this->scenarioGenerator->scenariosFromContentTypes($properties, $language_data, $content_type_data);

    // From paths.
    if (!empty($path_data) && trim($path_data[0]) !== '') {
      $this->scenarioGenerator->scenariosFromPaths($properties, $language_data, $path_data);
    }

    $message = t("Scenarios Generated for %label profile.", ['%label' => $values['label']]);
    $this->messenger()->addStatus($message);

    if ($rebuildForm) {
      // If we are rebuilding the form, we need to set the form state to
      // rebuild so that the new scenarios are reflected in the form.
      $form_state->setRebuild();
    }
  }

  /**
   * Updates the scenario URLs to use the test and reference domains.
   *
   * This method iterates through the list of scenarios and updates their URLs
   * to use the configured test and reference domains.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  private function updateScenarios(FormStateInterface $form_state): void {
    foreach ($form_state->getValue('scenario_list') as $scenario_id => $value) {
      $scenario = BackstopScenario::load($scenario_id);
      if ($scenario->get('override_profile_domains')) {
        continue;
      }
      if (!str_contains($scenario->get('url'), $form_state->getValue('test_domain'))) {
        $new_test_domain = $form_state->getValue('test_domain');
        $urlArray = parse_url($scenario->get('url'));
        $scenario->set('url', "{$new_test_domain}{$urlArray['path']}");
      }
      if (!str_contains($scenario->get('referenceUrl'), $form_state->getValue('reference_domain'))) {
        $new_reference_domain = $form_state->getValue('reference_domain');
        $urlArray = parse_url($scenario->get('referenceUrl'));
        $scenario->set('referenceUrl', "{$new_reference_domain}{$urlArray['path']}");
      }
      $scenario->save();
    }
  }

}
