<?php

declare(strict_types=1);

namespace Drupal\backstop_generator\Services;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Form builder for common Backstop Generator form elements and settings.
 */
final class BackstopFormBuilder {

  /**
   * Inline help text for asyncCaptureLimit field.
   *
   * @var array
   */
  protected array $asyncCaptureLimit = [
    'desc' => 'Maximum number of concurrent screenshot captures (helps optimize testing performance)',
    'link' => '/admin/help/backstop_generator#glossary-asyncCaptureLimit',
  ];

  /**
   * Inline help text for asyncCompareLimit field.
   *
   * @var array
   */
  protected array $asyncCompareLimit = [
    'desc' => 'Maximum number of concurrent image comparisons (helps to balance performance and resource usage)',
    'link' => '/admin/help/backstop_generator#glossary-asyncCompareLimit',
  ];

  /**
   * Inline help text for clickSelector field.
   *
   * @var array
   */
  protected array $clickSelector = [
    'desc' => 'Click the specified DOM element prior to screen shot',
    'link' => '/admin/help/backstop_generator#glossary-clickSelector',
    'placeholder' => '.example-button.submit',
  ];

  /**
   * Inline help text for clickSelectors field.
   *
   * @var array
   */
  protected array $clickSelectors = [
    'desc' => 'This is a comma-separated list of selectors to simulate multiple sequential click interactions.',
    'link' => '/admin/help/backstop_generator#glossary-clickSelector',
    'placeholder' => '.example-menu, .example-menu-item',
  ];

  /**
   * Inline help text for the cookiePath field.
   *
   * @var array
   */
  protected array $cookiePath = [
    'desc' => 'Import cookies in JSON format to get around the Accept Cookies screen',
    'link' => '/admin/help/backstop_generator#glossary-cookiePath',
    'placeholder' => '/path/to/cookies.json',
  ];

  /**
   * Inline help text for the debug field.
   *
   * @var array
   */
  protected array $debug = [
    'desc' => 'Enable debug mode to get detailed logs and troubleshooting information in the browser console during test execution.',
    'link' => '/admin/help/backstop_generator#glossary-debug',
  ];

  /**
   * Inline help text for the debugWindow field.
   *
   * @var array
   */
  protected array $debugWindow = [
    'desc' => 'Open a debug window for each test run to visually inspect the page and troubleshoot issues.',
    'link' => '/admin/help/backstop_generator#glossary-debugWindow',
  ];

  /**
   * Inline help text for the delay field.
   *
   * @var array
   */
  protected array $delay = [
    'desc' => 'The number of milliseconds to wait before testing.' .
    ' This can help ensure the page is fully loaded and stable before taking screenshots.',
    'link' => '/admin/help/backstop_generator#glossary-delay',
  ];

  /**
   * Inline help text for the expect field.
   *
   * @var array
   */
  protected array $engine = [
    'desc' => 'Select the browser engine for running tests.' .
    ' Choose between Puppeteer and Playwright.',
    'link' => '/admin/help/backstop_generator#glossary-engine',
  ];

  /**
   * Inline help text for the engineOptions field.
   *
   * @var array
   */
  protected array $engineOptions = [
    'desc' => 'A list of Chromium flags to send to the selected testing engine.' .
    ' Default --no-sandbox',
    'link' => '/admin/help/backstop_generator#glossary-engineOptions',
    'placeholder' => '--no-sandbox',
  ];

  /**
   * Inline help text for the expect field.
   *
   * @var array
   */
  protected array $expect = [
    'desc' => 'Define the expected number of elements to test for.',
    'link' => '/admin/help/backstop_generator#glossary-expect',
  ];

  /**
   * Inline help text for the gotoParameters field.
   *
   * @var array
   */
  protected array $gotoParameters = [
    'desc' => 'Pass custom lifecycle parameters to the goto function.' .
    ' Enter key/value pairs (key|value) on separate lines.',
    'link' => '/admin/help/backstop_generator#glossary-gotoParameters',
    'placeholder' => "example-parameter|example-setting\nwaitUntil|networkidle0\ntimeout|30000",
  ];

  /**
   * Inline help text for the hoverSelector field.
   *
   * @var array
   */
  protected array $hideSelectors = [
    'desc' => 'Comma-separated list of CSS selectors that will be set to display: none.' .
    ' These elements will be hidden before taking screenshots.',
    'link' => '/admin/help/backstop_generator#glossary-hideSelectors',
    'placeholder' => '.example-header, #example-main, [name=example-input]',
  ];

  /**
   * Inline help text for the hoverSelectors field.
   *
   * @var array
   */
  protected array $hoverSelector = [
    'desc' => 'Move the pointer over the specified DOM element prior to taking a screenshot.' .
    ' This can be useful for testing hover states or tooltips.',
    'link' => '/admin/help/backstop_generator#glossary-hoverSelector',
    'placeholder' => '.example-menu-item',
  ];

  /**
   * Inline help text for the hoverSelectors field.
   *
   * @var array
   */
  protected array $hoverSelectors = [
    'desc' => 'Comma-separated list of selectors to simulate multiple sequential hover interactions.' .
    ' Each selector will be hovered over in sequence before taking the screenshot.' .
    ' Note: Ensure that the elements are visible and interactable when hovered.' .
    ' This is useful for testing hover states or tooltips.',
    'link' => '/admin/help/backstop_generator#glossary-hoverSelector',
    'placeholder' => '.example-menu, #example-menu-item',
  ];

  /**
   * Inline help text for the keyPressSelectors field.
   *
   * @var array
   */
  protected array $keyPressSelectors = [
    'desc' => 'List of selectors to simulate multiple sequential keypress interactions.' .
    ' Each selector will be focused and the specified keyPress will be sent.' .
    ' Enter each selector and keyPress on a new line in the format: selector|keyPress.' .
    ' For example: .input-field|Enter.' .
    ' This allows for simulating user input and interactions with form fields or other elements.',
    'link' => '/admin/help/backstop_generator#glossary-keyPressSelectors',
    'placeholder' => ".example-input-field|Enter\n.example-button.submit|Space",
  ];

  /**
   * Inline help text for the misMatchThreshold field.
   *
   * @var array
   */
  protected array $misMatchThreshold = [
    'desc' => 'Set the trigger threshold as a percentage for image comparison.' .
    ' A lower value means stricter matching, while a higher value allows for more differences between the reference and test images.' .
    ' Default is 0.1%.',
    'link' => '/admin/help/backstop_generator#glossary-misMatchThreshold',
  ];

  /**
   * Inline help text for the postInteractionWait field.
   *
   * @var array
   */
  protected array $onBeforeScript = [
    'desc' => 'A custom script that runs before capturing screenshots.' .
    ' Use this to prepare global page states like logging in, clearing cookies, or setting up consistent testing conditions.',
    'link' => '/admin/help/backstop_generator#glossary-onBeforeScript',
  ];

  /**
   * Inline help text for the onReadyScript field.
   *
   * @var array
   */
  protected array $onReadyScript = [
    'desc' => 'A script that runs after the page load but before capturing screenshots.' .
    ' This can be used to handle global dynamic content, wait for site-wide animations, or ensure page stability across all scenarios.',
    'link' => '/admin/help/backstop_generator#glossary-onReadyScript',
  ];

  /**
   * Inline help text for the paths field.
   *
   * @var array
   */
  protected array $paths = [
    'desc' => 'Specify directory paths in key/value pairs on separate lines for storing BackstopJS test results and artifacts.' .
    ' Configure locations for reference screenshots, test runs, and test reports.' .
    ' By default, Backstop Generator will add the Profile id to the end of these paths to keep them unique.',
    'link' => '/admin/help/backstop_generator#glossary-paths',
    'placeholder' => "bitmaps_reference|/path/to/bitmaps_reference\n" .
    "bitmaps_test|/path/to/bitmaps_test/screenshots\n" .
    'Note: The Profile ID will be appended to these paths automatically.',
  ];

  /**
   * Inline help text for the postInteractionWait field.
   *
   * @var array
   */
  protected array $postInteractionWait = [
    'desc' => 'The number of milliseconds to wait after an interaction (like a click or hover) before taking a screenshot.' .
    ' This allows time for any animations or changes to occur on the page.',
    'link' => '/admin/help/backstop_generator#glossary-postInteractionWait',
  ];

  /**
   * Inline help text for the readyEvent field.
   *
   * @var array
   */
  protected array $readyEvent = [
    'desc' => 'Wait until this string has been logged to the console.' .
    ' This can be useful for ensuring that specific JavaScript events or conditions are met before taking a screenshot.' .
    ' Note: This should be a string that is logged to the console by your application or page.' .
    ' For example, if your application logs "Page ready" to the console when it is fully loaded, you can use that as the readyEvent.' .
    ' Make sure to include the exact string that will be logged.',
    'link' => '/admin/help/backstop_generator#glossary-readyEvent',
    'placeholder' => 'hello-world',
  ];

  /**
   * Inline help text for the readySelector field.
   *
   * @var array
   */
  protected array $readySelector = [
    'desc' => 'Wait until this selector exists before continuing.' .
    ' This is useful for ensuring that specific elements are present on the page before taking a screenshot.' .
    ' Note: This should be a CSS selector that is expected to appear on the page when it is fully loaded.' .
    ' For example, if your page displays a footer with the ID "example-footer" when it is ready, you can use that as the readySelector.' .
    ' Make sure to include the exact selector that will be present in the DOM when the page is ready.' .
    ' If no selector is specified, Backstop will wait indefinitely for any selector to appear.',
    'link' => '/admin/help/backstop_generator#glossary-readySelector',
    'placeholder' => '.example-main',
  ];

  /**
   * Inline help text for the readyTimeout field.
   *
   * @var array
   */
  protected array $readyTimeout = [
    'desc' => 'Timeout for readyEvent and readySelector.' .
    ' This is the maximum time to wait for the specified event or selector to appear before proceeding.' .
    ' If the timeout is reached, the test will continue regardless of whether the condition has been met.',
    'link' => '/admin/help/backstop_generator#glossary-readyTimeout',
  ];

  /**
   * Inline help text for the removeSelectors field.
   *
   * @var array
   */
  protected array $removeSelectors = [
    'desc' => 'Comma-separated list of CSS selectors that will be set to visibility: none.' .
    ' These elements will be removed from the page before taking screenshots.' .
    ' This is useful for removing elements that are not relevant to the test or may interfere with the screenshot.',
    'link' => '/admin/help/backstop_generator#glossary-removeSelectors',
    'placeholder' => '.example-header, #example-main, [name=example-input]',
  ];

  /**
   * Inline help text for the report field.
   *
   * @var array
   */
  protected array $report = [
    'desc' => 'Configure the output format and location of test reports.' .
    ' Choose how and where to generate reports, such as browser view or Continuous Integration formats.',
    'link' => '/admin/help/backstop_generator#glossary-report',
    'placeholder' => 'browser',
  ];

  /**
   * Inline help text for the requireSameDimensions field.
   *
   * @var array
   */
  protected array $requireSameDimensions = [
    'desc' => 'Check this to confirm that reference and test captures are the exact same dimensions.' .
    ' This is important for accurate image comparisons and ensuring that visual changes are detected correctly.',
    'link' => '/admin/help/backstop_generator#glossary-requireSameDimensions',
  ];

  /**
   * Inline help text for the scrollToSelector field.
   *
   * @var array
   */
  protected array $scrollToSelector = [
    'desc' => 'Scroll to the specified DOM element before taking a screenshot.' .
    ' This ensures that the element is in view and properly positioned within the viewport.',
    'link' => '/admin/help/backstop_generator#glossary-scrollToSelector',
    'placeholder' => '#example-footer',
  ];

  /**
   * Inline help text for the selectorExpansion field.
   *
   * @var array
   */
  protected array $selectorExpansion = [
    'desc' => 'Expand the specified selector to include all matching elements.' .
    ' This is useful for capturing multiple elements that match a given selector, such as all items in a list or all images in a gallery.',
    'link' => '/admin/help/backstop_generator#glossary-selectorExpansion',
  ];

  /**
   * Inline help text for the selectors field.
   *
   * @var array
   */
  protected array $selectors = [
    'desc' => 'A comma-separated list of CSS selectors to capture screenshots of.' .
    ' These selectors define the specific elements on the page that will be included in the test.' .
    ' You can specify multiple selectors to capture different parts of the page.',
    'link' => '/admin/help/backstop_generator#glossary-selectors',
    'placeholder' => '.example-header, #example-main, [name=example-input]',
  ];

  /**
   * Constructs a new BackstopForms object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly FileSystemInterface $fileSystem,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly TranslationInterface $t,
  ) {}

  /**
   * Returns the fields containing the URLs used for testing.
   *
   * @param array $form
   *   The form array.
   * @param array $defaults
   *   The default values for specific form fields.
   *
   * @return array
   *   The modified form array with the URL fields.
   */
  public function buildUrlSection(array $form, array $defaults = []): array {
    $server = $_SERVER;
    $site = "{$server['REQUEST_SCHEME']}://{$server['SERVER_NAME']}";
    $backstop_config = $this->configFactory->get('backstop_generator.settings');

    $form['test_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Test domain'),
      '#description' => $this->t('The domain you want to test - no trailing slash.'),
      '#description_display' => 'before',
      '#default_value' => $defaults['test_domain'] ?? $backstop_config->get('test_domain') ?? $site,
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'http://domain-to-test.com',
      ],
    ];

    $form['reference_domain'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Reference domain'),
      '#description' => $this->t('The domain you want to test against (source of truth) - no trailing slash.'),
      '#description_display' => 'before',
      '#default_value' => $defaults['reference_domain'] ?? $backstop_config->get('reference_domain') ?? '',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'https://domain-to-reference.com',
      ],
    ];

    return $form;
  }

  /**
   * Returns the Profile Parameters section of the form.
   *
   * @param array $form
   *   The form array.
   * @param array $defaults
   *   The default values for the form fields.
   * @param bool $forModuleSettings
   *   Whether the form is for module configuration settings or entity settings.
   *
   * @return array
   *   The modified form array with the Profile Parameters section.
   */
  public function buildProfileParametersSection(array $form, array $defaults = [], $forModuleSettings = FALSE): array {
    $config = $this->configFactory->get('backstop_generator.settings');

    // Initialize the form with the profile parameters.
    $form['profile_parameters'] = [
      '#type' => 'details',
      '#title' => $this->t('Profile Parameters'),
      '#open' => FALSE,
      '#description' => $this->t('Configure default settings for all new Profiles. These settings will be applied to any new Backstop profiles created.'),
      '#attributes' => ['id' => 'profile-parameters'],
      'advanced_settings' => [
        '#type' => 'details',
        '#title' => $this->t('Advanced settings'),
        '#open' => FALSE,
      ],
    ];

    // ===== asyncCaptureLimit =====
    $form['profile_parameters']['asyncCaptureLimit'] = [
      '#type' => 'number',
      '#title' => $this->t('asyncCaptureLimit'),
      '#description' => $this->getFieldDescription('asyncCaptureLimit'),
      '#description_display' => 'before',
      '#default_value' => $defaults['asyncCaptureLimit'] ?? $config->get('profile_parameters.asyncCaptureLimit') ?? 5,
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'asyncCaptureLimit',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
      ],
    ];

    // ===== asyncCompareLimit =====
    $form['profile_parameters']['asyncCompareLimit'] = [
      '#type' => 'number',
      '#title' => $this->t('asyncCompareLimit'),
      '#description' => $this->getFieldDescription('asyncCompareLimit'),
      '#description_display' => 'before',
      '#default_value' => $defaults['asyncCompareLimit'] ?? $config->get('profile_parameters.asyncCompareLimit') ?? 50,
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'asyncCompareLimit',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
      ],
    ];

    // ===== engine =====
    $form['profile_parameters']['engine'] = [
      '#type' => 'radios',
      '#title' => 'engine',
      '#default_value' => $defaults['engine'] ?? $config->get('profile_parameters.engine') ?? 'puppeteer',
      '#description' => $this->getFieldDescription('engine'),
      '#options' => [
        'puppeteer' => 'Puppeteer',
        'playwright' => 'Playwright',
      ],
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'engine',
      ] : NULL,
    ];

    // ===== engineOptions =====
    $form['profile_parameters']['advanced_settings']['engineOptions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('engineOptions') ?? $config->get('profile_parameters.engineOptions') ?? '--no-sandbox',
      '#description' => $this->getFieldDescription('engineOptions'),
      '#description_display' => 'before',
      '#default_value' => $defaults['engineOptions'] ?? '--no-sandbox',
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'engineOptions',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
      ],
    ];

    // ===== onBeforeScript =====
    $form['profile_parameters']['onBeforeScript'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include onBeforeScript'),
      '#description' => $this->getFieldDescription('onBeforeScript'),
      '#default_value' => $defaults['onBeforeScript'] ?? $config->get('profile_parameters.onBeforeScript') ?? FALSE,
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'onBeforeScript',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
      ],
    ];

    // ===== onReadyScript =====
    $form['profile_parameters']['onReadyScript'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include onReadyScript'),
      '#description' => $this->getFieldDescription('onReadyScript'),
      '#default_value' => $defaults['onReadyScript'] ?? $config->get('profile_parameters.onReadyScript') ?? FALSE,
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'onReadyScript',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
      ],
    ];

    // ===== paths =====
    $form['profile_parameters']['advanced_settings']['paths'] = [
      '#type' => 'textarea',
      '#default_value' => $defaults['paths'] ?? $config->get('profile_parameters.paths'),
    // '#description' => $this->getFieldDescription('paths'),
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'paths',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting hidden-field'],
      ],
    ];

    // ===== report =====
    $form['profile_parameters']['advanced_settings']['report'] = [
      '#type' => 'textfield',
      '#default_value' => $defaults['report'] ?? $config->get('profile_parameters.report') ?? 'browser',
    // '#description' => $this->getFieldDescription('report'),
      '#parents' => $forModuleSettings ? [
        'profile_parameters',
        'report',
      ] : NULL,
      '#attributes' => [
        'class' => ['advanced-setting'],
        'hidden' => TRUE,
      ],
    ];

    return $form;
  }

  /**
   * Builds the Scenario Defaults section of the form.
   *
   * @param array $form
   *   The form array.
   * @param array $defaults
   *   The default values for the form fields.
   * @param string|null $profile_id
   *   The ID of the profile providing scenarioDefault values.
   *
   * @return array
   *   The modified form array with the Scenario Defaults section.
   */
  public function buildScenarioDefaultsSection(array $form, array $defaults = [], $profile_id = NULL): array {
    $config = $this->configFactory->get('backstop_generator.settings');
    $profileDefaults = !is_null($profile_id) ? $this->profileScenarioDefaults($profile_id) : [];

    $form['scenario_defaults'] = [
      '#type' => 'details',
      '#title' => 'Scenario defaults',
      '#description' => $this->t('These settings apply to the scenarioDefaults section for new Profiles.'),
      '#open' => $profile_id ? TRUE : FALSE,
      '#states' => [
        'visible' => [
          ':input[name="useScenarioDefaults"]' => ['checked' => FALSE],
        ],
      ],
      'basic_settings' => [
        '#type' => 'details',
        '#title' => $this->t('Basic settings'),
        '#open' => TRUE,
      ],
      'advanced_settings' => [
        '#type' => 'details',
        '#title' => $this->t('Advanced settings'),
        '#open' => FALSE,
        'dom_events' => [
          '#type' => 'details',
          '#title' => $this->t('DOM events'),
          '#open' => FALSE,
          '#attributes' => ['id' => 'dom-events'],
        ],
        'user_behavior' => [
          '#type' => 'details',
          '#title' => $this->t('User behavior'),
          '#open' => FALSE,
        ],
      ],
    ];

    // ===== delay =====
    $form['scenario_defaults']['basic_settings']['delay'] = [
      '#type' => 'number',
      '#title' => $this->t('delay'),
      '#description' => $this->getFieldDescription('delay'),
      '#description_display' => 'before',
      '#min' => 0,
      '#max' => 10001,
      '#step' => 1,
      '#default_value' => is_null($profile_id) ?
      $defaults['delay'] ?? $config->get('scenarioDefaults.delay') :
      $defaults['delay'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'delay',
      ] : NULL,
    ];

    // ===== hideSelectors =====
    $form['scenario_defaults']['basic_settings']['hideSelectors'] = [
      '#type' => 'textfield',
      '#title' => $this->t('hideSelectors'),
      '#description' => $this->getFieldDescription('hideSelectors'),
      '#description_display' => 'before',
      '#default_value' => is_null($profile_id) ?
      $defaults['hideSelectors'] ?? $config->get('scenarioDefaults.hideSelectors') :
      $defaults['hideSelectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'hideSelectors',
      ] : NULL,
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['hideSelectors']) ? $profileDefaults['hideSelectors'] :
        $this->getFieldPlaceholder('hideSelectors'),
      ],
    ];

    // ===== removeSelectors =====
    $form['scenario_defaults']['basic_settings']['removeSelectors'] = [
      '#type' => 'textfield',
      '#title' => $this->t('removeSelectors'),
      '#description' => $this->getFieldDescription('removeSelectors'),
      '#description_display' => 'before',
      '#default_value' => is_null($profile_id) ?
      $defaults['removeSelectors'] ?? $config->get('scenarioDefaults.removeSelectors') :
      $defaults['removeSelectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'removeSelectors',
      ] : NULL,
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['removeSelectors']) ? $profileDefaults['removeSelectors'] :
        $this->getFieldPlaceholder('removeSelectors'),
      ],
    ];

    // ===== misMatchThreshold =====
    $form['scenario_defaults']['basic_settings']['misMatchThreshold'] = [
      '#type' => 'number',
      '#title' => $this->t('misMatchThreshold'),
      '#description' => $this->getFieldDescription('misMatchThreshold'),
      '#description_display' => 'before',
      '#min' => 0,
      '#max' => 100,
      '#step' => .01,
      '#default_value' => is_null($profile_id) ?
      $defaults['misMatchThreshold'] ?? $config->get('scenarioDefaults.misMatchThreshold') :
      $defaults['misMatchThreshold'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'misMatchThreshold',
      ] : NULL,
    ];

    // ===== requireSameDimensions =====
    $form['scenario_defaults']['basic_settings']['requireSameDimensions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('requireSameDimensions'),
      '#description' => $this->getFieldDescription('requireSameDimensions'),
      '#description_display' => 'before',
      '#default_value' => is_null($profile_id) ?
      $defaults['requireSameDimensions'] ?? $config->get('scenarioDefaults.requireSameDimensions') :
      $defaults['requireSameDimensions'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'requireSameDimensions',
      ] : NULL,
    ];

    // ===== cookiePath =====
    $form['scenario_defaults']['advanced_settings']['cookiePath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('cookiePath') ?? '/this/cookie/file/path',
      '#default_value' => is_null($profile_id) ?
      $defaults['cookiePath'] ?? $config->get('scenarioDefaults.cookiePath') :
      $defaults['cookiePath'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'cookiePath',
      ] : NULL,
      '#description' => $this->getFieldDescription('cookiePath'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['cookiePath']) ? $profileDefaults['cookiePath'] :
        $this->getFieldPlaceholder('cookiePath'),
      ],
    ];

    // ===== readyEvent =====
    $form['scenario_defaults']['advanced_settings']['dom_events']['readyEvent'] = [
      '#type' => 'textfield',
      '#title' => $this->t('readyEvent'),
      '#default_value' => is_null($profile_id) ?
      $defaults['readyEvent'] ?? $config->get('scenarioDefaults.readyEvent') :
      $defaults['readyEvent'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'readyEvent',
      ] : NULL,
      '#description' => $this->getFieldDescription('readyEvent'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['readyEvent']) ? $profileDefaults['readyEvent'] :
        $this->getFieldPlaceholder('readyEvent'),
      ],
    ];

    // ===== readySelector =====
    $form['scenario_defaults']['advanced_settings']['dom_events']['readySelector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('readySelector'),
      '#default_value' => is_null($profile_id) ?
      $defaults['readySelector'] ?? $config->get('scenarioDefaults.readySelector') :
      $defaults['readySelector'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'readySelector',
      ] : NULL,
      '#description' => $this->getFieldDescription('readySelector'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['readySelector']) ? $profileDefaults['readySelector'] :
        $this->getFieldPlaceholder('readySelector'),
      ],
    ];

    // ===== readyTimeout =====
    $form['scenario_defaults']['advanced_settings']['dom_events']['readyTimeout'] = [
      '#type' => 'number',
      '#title' => $this->t('readyTimeout'),
      '#default_value' => is_null($profile_id) ?
      $defaults['readyTimeout'] ?? $config->get('scenarioDefaults.readyTimeout') :
      $defaults['readyTimeout'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'readyTimeout',
      ] : NULL,
      '#description' => $this->getFieldDescription('readyTimeout'),
      '#field_suffix' => 'ms',
      '#description_display' => 'before',
    ];

    // ===== onBeforeScript =====
    $form['scenario_defaults']['advanced_settings']['dom_events']['onBeforeScript'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include onBeforeScript'),
      '#default_value' => is_null($profile_id) ?
      $defaults['onBeforeScript'] ?? $config->get('scenarioDefaults.onBeforeScript') :
      $defaults['onBeforeScript'] ?? NULL,
      '#description' => $this->getFieldDescription('onBeforeScript'),
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'onBeforeScript',
      ] : NULL,
    ];

    // ===== onReadyScript =====
    $form['scenario_defaults']['advanced_settings']['dom_events']['onReadyScript'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include onReadyScript'),
      '#default_value' => is_null($profile_id) ?
      $defaults['onReadyScript'] ?? $config->get('scenarioDefaults.onReadyScript') :
      $defaults['onReadyScript'] ?? NULL,
      '#description' => $this->getFieldDescription('onReadyScript'),
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'onReadyScript',
      ] : NULL,
    ];

    // ===== keyPressSelectors =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['keyPressSelectors'] = [
      '#type' => 'textarea',
      '#title' => $this->t('keyPressSelectors'),
      '#default_value' => is_null($profile_id) ?
      $defaults['keyPressSelectors'] ?? $config->get('scenarioDefaults.keyPressSelectors') :
      $defaults['keyPressSelectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'keyPressSelectors',
      ] : NULL,
      '#description' => $this->getFieldDescription('keyPressSelectors'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['keyPressSelectors']) ? $profileDefaults['keyPressSelectors'] :
        $this->getFieldPlaceholder('keyPressSelectors'),
      ],
    ];

    // ===== hoverSelector =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['hoverSelector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('hoverSelector'),
      '#default_value' => is_null($profile_id) ?
      $defaults['hoverSelector'] ?? $config->get('scenarioDefaults.hoverSelector') :
      $defaults['hoverSelector'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'hoverSelector',
      ] : NULL,
      '#description' => $this->getFieldDescription('hoverSelector'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['hoverSelector']) ? $profileDefaults['hoverSelector'] :
        $this->getFieldPlaceholder('hoverSelector'),
      ],
    ];

    // ===== hoverSelectors =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['hoverSelectors'] = [
      '#type' => 'textfield',
      '#title' => $this->t('hoverSelectors'),
      '#default_value' => is_null($profile_id) ?
      $defaults['hoverSelectors'] ?? $config->get('scenarioDefaults.hoverSelectors') :
      $defaults['hoverSelectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'hoverSelectors',
      ] : NULL,
      '#description' => $this->getFieldDescription('hoverSelectors'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['hoverSelectors']) ? $profileDefaults['hoverSelectors'] :
        $this->getFieldPlaceholder('hoverSelectors'),
      ],
    ];

    // ===== clickSelector =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['clickSelector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('clickSelector'),
      '#default_value' => is_null($profile_id) ?
      $defaults['clickSelector'] ?? $config->get('scenarioDefaults.clickSelector') :
      $defaults['clickSelector'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'clickSelector',
      ] : NULL,
      '#description' => $this->getFieldDescription('clickSelector'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['clickSelector']) ? $profileDefaults['clickSelector'] :
        $this->getFieldPlaceholder('clickSelector'),
      ],
    ];

    // ===== clickSelectors =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['clickSelectors'] = [
      '#type' => 'textfield',
      '#title' => $this->t('clickSelectors'),
      '#default_value' => is_null($profile_id) ?
      $defaults['clickSelectors'] ?? $config->get('scenarioDefaults.clickSelectors') :
      $defaults['clickSelectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'clickSelectors',
      ] : NULL,
      '#description' => $this->getFieldDescription('clickSelectors'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['clickSelectors']) ? $profileDefaults['clickSelectors'] :
        $this->getFieldPlaceholder('clickSelectors'),
      ],
    ];

    // ===== postInteractionWait =====
    $form['scenario_defaults']['advanced_settings']['user_behavior']['postInteractionWait'] = [
      '#type' => 'number',
      '#title' => $this->t('postInteractionWait'),
      '#default_value' => is_null($profile_id) ?
      $defaults['postInteractionWait'] ?? $config->get('scenarioDefaults.postInteractionWait') :
      $defaults['postInteractionWait'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'postInteractionWait',
      ] : NULL,
      '#description' => $this->getFieldDescription('postInteractionWait'),
      '#description_display' => 'before',
    ];

    // ===== scrollToSelector =====
    $form['scenario_defaults']['advanced_settings']['scrollToSelector'] = [
      '#type' => 'textfield',
      '#title' => $this->t('scrollToSelector'),
      '#default_value' => is_null($profile_id) ?
      $defaults['scrollToSelector'] ?? $config->get('scenarioDefaults.scrollToSelector') :
      $defaults['scrollToSelector'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'scrollToSelector',
      ] : NULL,
      '#description' => $this->getFieldDescription('scrollToSelector'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['scrollToSelector']) ? $profileDefaults['scrollToSelector'] :
        $this->getFieldPlaceholder('scrollToSelector'),
      ],
    ];

    // ===== selectors =====
    $form['scenario_defaults']['advanced_settings']['selectors'] = [
      '#type' => 'textfield',
      '#title' => $this->t('selectors'),
      '#default_value' => is_null($profile_id) ?
      $defaults['selectors'] ?? $config->get('scenarioDefaults.selectors') :
      $defaults['selectors'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'selectors',
      ] : NULL,
      '#description' => $this->getFieldDescription('selectors'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['selectors']) ? $profileDefaults['selectors'] :
        $this->getFieldPlaceholder('selectors'),
      ],
    ];

    // ===== selectorExpansion =====
    $form['scenario_defaults']['advanced_settings']['selectorExpansion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('selectorExpansion'),
      '#default_value' => is_null($profile_id) ?
      $defaults['selectorExpansion'] ?? $config->get('scenarioDefaults.selectorExpansion') :
      $defaults['selectorExpansion'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'selectorExpansion',
      ] : NULL,
      '#description' => $this->getFieldDescription('selectorExpansion'),
      '#return_value' => 1,
    ];

    // ===== expect =====
    $form['scenario_defaults']['advanced_settings']['expect'] = [
      '#type' => 'number',
      '#title' => $this->t('expect'),
      '#default_value' => is_null($profile_id) ?
      $defaults['expect'] ?? $config->get('scenarioDefaults.expect') :
      $defaults['expect'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'expect',
      ] : NULL,
      '#description' => $this->getFieldDescription('expect'),
      '#description_display' => 'before',
    ];

    // ===== gotoParameters =====
    $form['scenario_defaults']['advanced_settings']['gotoParameters'] = [
      '#type' => 'textarea',
      '#title' => $this->t('gotoParameters'),
      '#default_value' => is_null($profile_id) ?
      $defaults['gotoParameters'] ?? $config->get('scenarioDefaults.gotoParameters') :
      $defaults['gotoParameters'] ?? NULL,
      '#parents' => is_null($profile_id) ? [
        'scenarioDefaults',
        'gotoParameters',
      ] : NULL,
      '#description' => $this->getFieldDescription('gotoParameters'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => !empty($profileDefaults['gotoParameters']) ? $profileDefaults['gotoParameters'] :
        $this->getFieldPlaceholder('gotoParameters'),
      ],
    ];

    return $form;
  }

  /**
   * Translates a string using Drupal's translation system.
   *
   * @param string $string
   *   The string to translate.
   * @param array $args
   *   An array of arguments to replace in the string.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The translated string.
   */
  protected function t(string $string, array $args = []): TranslatableMarkup {
    return $this->t->translate($string, $args);
  }

  /**
   * Returns the description for a given field.
   *
   * @param string $field_name
   *   The name of the field to get the description for.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The description for the field, or a default message if not available.
   */
  public function getFieldDescription(string $field_name): TranslatableMarkup {
    if (property_exists($this, $field_name)) {
      return $this->t(
        "@description @link",
        [
          '@description' => $this->{$field_name}['desc'],
          '@link' => "<a href=\"{$this->$field_name['link']}\">See Glossary</a>",
        ]
      );
    }
    return $this->t('No description available for this field. Refer to the <a href="/admin/help/backstop_generator">Backstop Generator documentation</a> for more information on how to use this field and its purpose.');
  }

  /**
   * Returns the placeholder for a given field.
   *
   * @param string $field_name
   *   The name of the field to get the placeholder for.
   *
   * @return string|null
   *   The placeholder for the field, or NULL if not available.
   */
  protected function getFieldPlaceholder(string $field_name): ?string {
    if (!property_exists($this, $field_name)) {
      return NULL;
    }

    $field = $this->$field_name;

    if (is_array($field) && array_key_exists('placeholder', $field)) {
      return $field['placeholder'];
    }

    return NULL;
  }

  /**
   * Returns the scenario defaults for a given profile ID.
   *
   * This is used in the Scenario edit form to set the placeholder text equal
   * to the current defaults for that profile. This allows users to see
   * the existing defaults when editing a scenario.
   *
   * @param string $profile_id
   *   The ID of the profile to get the scenario defaults for.
   *
   * @return array
   *   The scenario defaults for the specified profile.
   */
  protected function profileScenarioDefaults(string $profile_id): array {
    $config = $this->configFactory->get('backstop_generator.profile.' . $profile_id);
    return $config->get('scenarioDefaults') ?? [];
  }

}
