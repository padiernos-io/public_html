<?php

namespace Drupal\alt_login;

use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager Alt login methods.
 *
 * @todo we probably don't need this at all - just need to put the alter hook somewhere
 */
class AltLoginMethodManager extends DefaultPluginManager{

  private $config;

  /**
   * Constructor.
   */
  public function __construct($namespaces, $module_handler, $config_factory) {
    parent::__construct(
      'Plugin/AltLoginMethod',
      $namespaces,
      $module_handler,
      AltLoginMethodInterface::class,
      Attribute\AltLoginMethod::class,
      '\Drupal\alt_login\Annotation\AltLoginMethod'
    );
    // NB This is a protected method
    $this->alterInfo('alt_login_info');
    $this->config = $config_factory->get('alt_login.settings');
  }

  /**
   * Get the names of the config items.
   *
   * @return array
   *   The names of all the plugins, keyed by ID.
   */
  public function getOptions() {
    foreach ($this->getDefinitions() as $id => $def) {
      // Is this translated?
      $names[$id] = $def['label'];
    }
    return $names;
  }

  public function getActiveLabels() {
    foreach ($this->config->get('aliases') as $plugin_id) {
      $def = $this->getDefinition($plugin_id);
      $labels[$plugin_id] = strtolower($def['label']);
    }
    return $labels;
  }

  /**
   * Utility
   *
   * Load all the active plugins()
   *
   * @return AltLoginMethodInterface[]
   */
  function activePlugins() {
    foreach ($this->config->get('aliases') as $plugin_id) {
      $plugins[$plugin_id] = $this->createInstance($plugin_id);
    }
    return $plugins;
  }

  function getDefinitions() {
    $definitions = parent::getDefinitions();
    // put the username last.
    if (isset($definitions['username'])) {
      $def = $definitions['username'];
      unset($definitions['username']);
      $definitions['username'] = $def;
    }
    if (!\Drupal::moduleHandler()->moduleExists('address')) {
      unset($definitions['address_name']);
    }
    return $definitions;
  }


}
