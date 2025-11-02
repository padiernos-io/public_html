<?php

namespace Drupal\backstop_generator;

/**
 * This is the template to start the construction of each scenario.
 */
class BackstopJsonTemplate {

  /**
   * The id of the Profile.
   *
   * @var string
   */
  public string $id;

  /**
   * The array of viewports to be tested.
   *
   * @var array
   */
  public array $viewports;

  /**
   * The file name and path of the onBeforeScript.
   *
   * @var string
   */
  public string $onBeforeScript;

  /**
   * The file name and path of the onReadyScript.
   *
   * @var string
   */
  public string $onReadyScript;

  /**
   * The array of values that apply to all scenarios.
   *
   * @var array
   */
  public array $scenarioDefaults;

  /**
   * The array of scenarios to run during testing.
   *
   * @var array
   */
  public array $scenarios;

  /**
   * The paths where test results will be saved.
   *
   * @var array
   */
  public array $paths;

  /**
   * Where to display the test results.
   *
   * @var array
   */
  public array $report;

  /**
   * The name of the engine this profile uses.
   *
   * @var string
   */
  public string $engine;

  /**
   * The array of options available.
   *
   * @var array
   */
  public array $engineOptions;

  /**
   * The number of images to capture when running the 'reference' command.
   *
   * @var int
   */
  public int $asyncCaptureLimit;

  /**
   * The number of images to compare at once when running the 'test' command.
   *
   * @var int
   */
  public int $asyncCompareLimit;

  /**
   * Flag that determines whether to display error messages to the console.
   *
   * @var bool
   */
  public bool $debug;

  /**
   * Flag that determines whether to open a debugging window in the browser.
   *
   * @var bool
   */
  public bool $debugWindow;

  /**
   * Constructs a new BackstopJsonTemplate object.
   *
   * @param string $id
   *   The ID to check.
   */
  public function __construct(string $id) {
    $this->id = $id;
    $this->scenarioDefaults = [];
    $this->paths = [
      'bitmaps_reference' => 'backstop_data/bitmaps_reference',
      'bitmaps_test' => 'backstop_data/bitmaps_test',
      'engine_scripts' => 'backstop_data/engine_scripts',
      'html_report' => "backstop_data/html_report_{$this->id}/",
      'ci_report' => "backstop_data/ci_report_{$this->id}/",
    ];
    $this->report = ['browser'];
    $this->engineOptions = [
      "args" => [
        "--no-sandbox",
      ],
    ];
    $this->asyncCaptureLimit = 5;
    $this->asyncCompareLimit = 50;
    $this->debug = FALSE;
    $this->debugWindow = FALSE;
  }

  /**
   * Return the value requested by $key.
   *
   * @param string $key
   *   The key value.
   *
   * @return mixed
   *   Returns the object of the key
   */
  public function get(string $key): mixed {
    return $this->$key;
  }

  /**
   * Sets the given key to the value.
   *
   * @param string $key
   *   This is the key.
   * @param mixed $value
   *   This is the value.
   *
   * @return void
   *   Does not return anything
   */
  public function set(string $key, $value): void {
    $this->$key = $value;
  }

  /**
   * Return the JSON formatted string of this object.
   *
   * @return bool|string
   *   Return the JSON formatted object
   */
  public function json(): bool|string {
    return json_encode(
      $this,
      JSON_PRETTY_PRINT |
      JSON_UNESCAPED_SLASHES |
      JSON_UNESCAPED_UNICODE |
      JSON_NUMERIC_CHECK
    );
  }

}
