<?php

namespace Drupal\backstop_generator\Entity;

use Drupal\backstop_generator\BackstopScenarioInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the backstop scenario entity type.
 *
 * @ConfigEntityType(
 *   id = "backstop_scenario",
 *   label = @Translation("Backstop Scenario"),
 *   label_collection = @Translation("Backstop Scenarios"),
 *   label_singular = @Translation("backstop scenario"),
 *   label_plural = @Translation("backstop scenarios"),
 *   label_count = @PluralTranslation(
 *     singular = "@count backstop scenario",
 *     plural = "@count backstop scenarios",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\backstop_generator\BackstopScenarioListBuilder",
 *     "form" = {
 *       "add" = "Drupal\backstop_generator\Form\BackstopScenarioForm",
 *       "edit" = "Drupal\backstop_generator\Form\BackstopScenarioForm",
 *       "delete" = "Drupal\backstop_generator\Form\BackstopScenarioDeleteForm"
 *     }
 *   },
 *   config_prefix = "scenario",
 *   admin_permission = "administer backstop_generator",
 *   links = {
 *     "collection" = "/admin/config/development/backstop-generator/scenarios",
 *     "add-form" = "/admin/config/development/backstop-generator/scenario/add",
 *     "edit-form" = "/admin/config/development/backstop-generator/scenario/{backstop_scenario}",
 *     "delete-form" = "/admin/config/development/backstop-generator/scenario/{backstop_scenario}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "page",
 *     "id",
 *     "label",
 *     "profile_id",
 *     "useScenarioDefaults",
 *     "locked",
 *     "description",
 *     "bundle",
 *     "onBeforeScript",
 *     "onReadyScript",
 *     "cookiePath",
 *     "override_profile_domains",
 *     "url",
 *     "referenceUrl",
 *     "readyEvent",
 *     "readySelector",
 *     "readyTimeout",
 *     "delay",
 *     "hideSelectors",
 *     "removeSelectors",
 *     "keyPressSelectors",
 *     "hoverSelector",
 *     "hoverSelectors",
 *     "clickSelector",
 *     "clickSelectors",
 *     "postInteractionWait",
 *     "scrollToSelector",
 *     "selectors",
 *     "selectorExpansion",
 *     "expect",
 *     "misMatchThreshold",
 *     "requireSameDimensions",
 *     "viewports",
 *     "gotoParameters"
 *   }
 * )
 */
class BackstopScenario extends ConfigEntityBase implements BackstopScenarioInterface {

  /**
   * The nid of the page to test.
   *
   * @var int
   */
  protected $page;

  /**
   * The backstop scenario ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The backstop scenario label.
   *
   * Also the tag saved with your reference images.
   *
   * @var string
   */
  protected $label;

  /**
   * The ID of the profile referencing this scenario.
   *
   * @var string
   */
  protected $profile_id;

  /**
   * A flag indicating whether to use Profile default settings.
   *
   * @var bool
   */
  protected $useScenarioDefaults = TRUE;

  /**
   * The backstop scenario status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The backstop_scenario description.
   *
   * @var string
   */
  protected $description;

  /**
   * The bundle name of the referenced node.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Determines whether to include the onBeforeScript.
   *
   * @var bool
   */
  protected $onBeforeScript;

  /**
   * Determines whether to include the onReadyScript.
   *
   * @var bool
   */
  protected $onReadyScript;

  /**
   * Import cookies in JSON format.
   *
   * @var string
   */
  protected $cookiePath;

  /**
   * Flag to indicate if the Profile domains have been overridden.
   *
   * @var bool
   */
  public $override_profile_domains;

  /**
   * The url of your app state.
   *
   * @var string
   */
  protected $url;

  /**
   * Specify a different state or environment when creating reference.
   *
   * @var string
   */
  protected $referenceUrl;

  /**
   * Wait until this string has been logged to the console.
   *
   * @var string
   */
  protected $readyEvent;

  /**
   * Wait until this selector exists before continuing.
   *
   * @var string
   */
  protected $readySelector;

  /**
   * Timeout for readyEvent and readySelector.
   *
   * @var int
   */
  protected $readyTimeout;

  /**
   * Wait for x milliseconds.
   *
   * @var int
   */
  protected $delay;

  /**
   * Array of selectors set to visibility: hidden.
   *
   * @var array
   */
  protected $hideSelectors;

  /**
   * Array of selectors set to display: none.
   *
   * @var array
   */
  protected $removeSelectors;

  /**
   * List of selectors to simulate multiple sequential keypress interactions.
   *
   * @var array
   */
  protected $keyPressSelectors;

  /**
   * Move the pointer over the specified DOM element prior to screen shot.
   *
   * @var string
   */
  protected $hoverSelector;

  /**
   * Selectors to simulate multiple sequential hover interactions.
   *
   * @var array
   */
  protected $hoverSelectors;

  /**
   * Click the specified DOM element prior to screen shot.
   *
   * @var string
   */
  protected $clickSelector;

  /**
   * Selectors to simulate multiple sequential click interactions.
   *
   * @var array
   */
  protected $clickSelectors;

  /**
   * Wait for a selector after interacting with hoverSelector or clickSelector.
   *
   * @var string
   */
  protected $postInteractionWait;

  /**
   * Scrolls the specified DOM element into view prior to screen shot.
   *
   * Available with default onReadyScript.
   *
   * @var string
   */
  protected $scrollToSelector;

  /**
   * Array of selectors to capture.
   *
   * @var array
   */
  protected $selectors;

  /**
   * Whether to take screenshots of designated selectors.
   *
   * @var bool
   */
  protected $selectorExpansion;

  /**
   * The number of selector elements to test for.
   *
   * @var int
   */
  protected $expect;

  /**
   * Percentage of different pixels allowed to pass test.
   *
   * @var int
   */
  protected $misMatchThreshold;

  /**
   * If set to true -- any change in selector size will trigger a test failure.
   *
   * @var bool
   */
  protected $requireSameDimensions;

  /**
   * An array of screen size objects your DOM will be tested against.
   *
   * @var array
   */
  protected $viewports;

  /**
   * An array of settings passed to page.goto(url, parameters) function.
   *
   * @var array
   */
  protected $gotoParameters;

  /**
   * Determine if the key belongs in the JSON file.
   *
   * @return array
   *   An associative array of scenario values to be included in the JSON file.
   */
  public function getJsonKeyValues(): array {
    $non_json_keys = [
      'id', 'uuid', 'status', 'dependencies', 'langcode', 'profile_id', 'bundle',
      'page', 'locked', 'useScenarioDefaults', 'description', 'override_urls',
    ];

    switch ($this->useScenarioDefaults) {
      case FALSE:
        $json_keys = array_filter(
          $this->toArray(),
          function ($key) use ($non_json_keys) {
            return !in_array($key, $non_json_keys);
          },
          ARRAY_FILTER_USE_KEY,
        );
        return $this->processScenarioValues($json_keys);

      default:
        return [
          'label' => $this->label,
          'url' => $this->url,
          'referenceUrl' => $this->referenceUrl,
        ];
    }
  }

  /**
   * Determine if the key belongs in the JSON file.
   *
   * @param string $key
   *   The key to check.
   *
   * @return bool
   *   TRUE if the key belongs in the JSON file, FALSE otherwise.
   */
  public function isJsonKey(string $key): bool {
    $default_vals = [
      'onBeforeScript' => $this->onBeforeScript,
      'onReadyScript' => $this->onReadyScript,
      'cookiePath' => $this->cookiePath,
      'readyEvent' => $this->readyEvent,
      'readySelector' => $this->readySelector,
      'readyTimeout' => $this->readyTimeout,
      'delay' => $this->delay,
      'hideSelectors' => $this->hideSelectors,
      'removeSelectors' => $this->removeSelectors,
      'keyPressSelectors' => $this->keyPressSelectors,
      'hoverSelector' => $this->hoverSelector,
      'hoverSelectors' => $this->hoverSelectors,
      'clickSelector' => $this->clickSelector,
      'clickSelectors' => $this->clickSelectors,
      'postInteractionWait' => $this->postInteractionWait,
      'scrollToSelector' => $this->scrollToSelector,
      'selectors' => $this->selectors,
      'selectorExpansion' => $this->selectorExpansion,
      'expect' => $this->expect,
      'misMatchThreshold' => $this->misMatchThreshold,
      'requireSameDimensions' => $this->requireSameDimensions,
      'viewports' => $this->viewports,
    ];
    return in_array($key, $default_vals);
  }

  /**
   * Process the scenario values.
   *
   * @param array $values
   *   The values to process.
   *
   * @return array
   *   The processed values.
   */
  public function processScenarioValues(array $values): array {
    return backstop_generator_process_scenario_values($values, $this->profile_id);
  }

}
