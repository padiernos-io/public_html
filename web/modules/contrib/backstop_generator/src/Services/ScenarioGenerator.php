<?php

namespace Drupal\backstop_generator\Services;

use Drupal\backstop_generator\Entity\BackstopScenario;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Service class for bulk generating Scenario entities.
 *
 * @package Drupal\backstop_generator\Services
 */
class ScenarioGenerator {

  /**
   * The configuration factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger channel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * The path alias manager service.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $pathAliasManager;

  /**
   * The menu node data service.
   *
   * @var \Drupal\backstop_generator\Services\MenuNodeData
   */
  protected $menuNodeData;

  /**
   * The random node list service.
   *
   * @var \Drupal\backstop_generator\Services\RandomNodeList
   */
  protected $randomNodeList;

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
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Backstop Generator module configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $backstop;

  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannel $logger,
    AliasManagerInterface $path_alias_manager,
    MenuNodeData $menu_node_data,
    RandomNodeList $random_node_list,
    LanguageManagerInterface $language_manager,
    ModuleHandlerInterface $module_handler,
    MessengerInterface $messenger,
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->pathAliasManager = $path_alias_manager;
    $this->menuNodeData = $menu_node_data;
    $this->randomNodeList = $random_node_list;
    $this->languageManager = $language_manager;
    $this->moduleHandler = $module_handler;
    $this->messenger = $messenger;
    $this->backstop = $this->configFactory->get('backstop_generator.settings');
  }

  /**
   * Create scenarios for the homepage in all selected languages.
   *
   * @param array $properties
   *   The properties for the scenario.
   * @param array $language_data
   *   The list of languages to create scenarios for.
   *   The array requires two keys: language_list (an array of language IDs)
   *   and default_language (the ID of the default language).
   */
  public function scenariosFromHomepage(array $properties, array $language_data) {
    // Create the homepage scenario for each selected language.
    foreach ($language_data['language_list'] as $lang_id) {
      $properties['langcode'] = $lang_id;
      // Create the homepage scenario for the default language.
      if ($lang_id == $language_data['default_language']) {
        $properties['url'] .= '/';
        $properties['referenceUrl'] .= '/';
        $properties['bundle'] = 'front';
        $this->createScenario("{$properties['profile_id']}_home", 'Home', $properties);
        continue;
      }
      $properties['url'] .= "$lang_id/";
      $properties['referenceUrl'] .= "$lang_id/";
      $properties['bundle'] = "front-{$lang_id}";
      $this->createScenario("{$properties['profile_id']}_{$lang_id}_home", "Home", $properties);
    }
  }

  /**
   * Create scenarios based on menu paths.
   *
   * @param array $properties
   *   The basic properties for the scenario.
   * @param array $language_data
   *   The list of languages to create scenarios for.
   * @param array $menu_data
   *   The list of menus and menu depth to create scenarios for.
   */
  public function scenariosFromMenus(array $properties, array $language_data, array $menu_data) {
    foreach ($language_data['language_list'] as $lang_id) {
      $properties['langcode'] = $lang_id;

      foreach ($menu_data['menu_list'] as $menu_id) {
        // Create the scenarios based on menu paths.
        $default_lang_id = $this->languageManager->getDefaultLanguage()->getId();
        $paths = $this->menuNodeData->getMenuLinkPaths($menu_id, $menu_data['menu_depth']);
        foreach ($paths as $menu_path) {
          // Reset the properties for each menu path.
          $properties['url'] = $this->backstop->get('test_domain');
          $properties['referenceUrl'] = $this->backstop->get('reference_domain');

          $path = $menu_path['path'] == '/' ?
            'home' :
            $this->extractPath($menu_path['path'], $lang_id);

          // Remove characters not allowed in a config entity name.
          $path_config_name = $this->cleanPath($path);

          // Concatenate the path to the domains.
          $properties['url'] .= $path;
          $properties['referenceUrl'] .= $path;

          // Add the bundle name for use in the label.
          $properties['bundle'] = $menu_path['bundle'];
          $label = $lang_id == $default_lang_id ?
            "menu:$menu_id:{$menu_path['bundle']}:{$menu_path['title']}" :
            "menu-$lang_id:$menu_id:{$menu_path['bundle']}:{$menu_path['title']}";

          // Create the scenario for each language.
          if ($lang_id == $default_lang_id) {
            $this->createScenario("{$properties['profile_id']}_{$path_config_name}", $label, $properties);
          }
          else {
            $properties['url'] .= "/";
            $properties['referenceUrl'] .= "/";
            $this->createScenario("{$properties['profile_id']}_{$lang_id}_{$path_config_name}", $label, $properties);
          }
        }
      }
    }
  }

  /**
   * Create scenarios based on content types.
   *
   * @param array $properties
   *   The properties for the scenario.
   * @param array $language_data
   *   The list of languages to create scenarios for.
   * @param array $content_types_data
   *   The list of content types and node quantity to create scenarios for.
   */
  public function scenariosFromContentTypes(array $properties, array $language_data, array $content_types_data) {
    $content_types = $content_types_data['content_type_list'];
    $quantity = $content_types_data['node_quantity'];

    // Create the list of random nodes by content_type.
    $content_type_nids = [];
    foreach ($content_types as $content_type) {
      $content_type_nids = array_merge($content_type_nids, $this->randomNodeList->getRandomNodes($content_type, $quantity));
    }

    // Create the scenarios based on content type and language.
    foreach ($language_data['language_list'] as $lid) {
      foreach ($content_type_nids as $node) {
        // Clear the value from the previous iteration.
        $properties['url'] = $this->backstop->get('test_domain');
        $properties['referenceUrl'] = $this->backstop->get('reference_domain');

        switch ($lid == $language_data['default_language']) {
          case TRUE:
            $id = "node-{$node['nid']}";
            $properties['url'] .= "/node/{$node['nid']}";
            $properties['referenceUrl'] .= "/node/{$node['nid']}";
            $properties['bundle'] = $node['bundle'];
            $label = "node:{$node['bundle']}:{$node['title']}";
            $this->createScenario("{$properties['profile_id']}_$id", $label, $properties);
            break;

          case FALSE:
            $id = "node-{$node['nid']}-{$lid}";
            $properties['url'] .= "/{$lid}/node/{$node['nid']}";
            $properties['referenceUrl'] .= "/{$lid}/node/{$node['nid']}";
            $properties['bundle'] = $node['bundle'];
            $label = "node-$lid:{$node['bundle']}:{$node['title']}";
            $this->createScenario("{$properties['profile_id']}_$id", $label, $properties);
            break;
        }
      }
    }
  }

  /**
   * Create scenarios based on specific paths.
   *
   * @param array $properties
   *   The properties for the scenario.
   * @param array $language_data
   *   The list of languages to create scenarios for.
   * @param array $paths
   *   The list of paths to create scenarios for.
   */
  public function scenariosFromPaths(array $properties, array $language_data, array $paths) {

    foreach ($language_data['language_list'] as $lang_id) {
      // Set the language code for the current iteration.
      $properties['langcode'] = $lang_id;
      $is_default = $lang_id == $language_data['default_language'];

      foreach ($paths as $path) {
        // Clear the value from the previous iteration.
        $properties['url'] = $this->backstop->get('test_domain');
        $properties['referenceUrl'] = $this->backstop->get('reference_domain');

        // Unset the bundle key from the previous iteration.
        unset($properties['bundle']);

        // Get the node information if it exists.
        if (preg_match('/node\/(\d+)/', $path, $node_path)) {
          $node = Node::load($node_path[1]);
          $properties['bundle'] = $node->bundle();
        }
        else {
          // Get the internal path (node/[nid]) from an alias.
          $path = $this->pathAliasManager->getPathByAlias($path);
        }

        // Parse the 'Label | node/1' format into parts.
        preg_match('/(.+)\s\|\s(.+)/', $path, $path_matches);
        if (!$path_matches) {
          continue;
        }
        $label = $is_default ?
          "path:{$path_matches[1]}" :
          "path-$lang_id:{$path_matches[1]}";
        $test_path = $is_default ?
          $path_matches[2] :
          "{$lang_id}/{$path_matches[2]}";

        $id = isset($properties['bundle']) ?
          // Replace slashes with hyphens for typical path.
          str_replace('/', '-', $test_path) :
          // Replace the path with a hash for paths with query strings.
          substr(md5($path_matches[0]), 0, 9);

        $properties['url'] .= "/$test_path";
        $properties['referenceUrl'] .= "/$test_path";

        $this->createScenario("{$properties['profile_id']}_$id", $label, $properties);
      }

    }
  }

  /**
   * Create a new backstop scenario entity.
   *
   * @param string $id
   *   The ID of the scenario.
   * @param string $label
   *   The label of the scenario.
   * @param array $properties
   *   The properties of the scenario.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface
   *   The created scenario entity or FALSE on failure.
   */
  private function createScenario(string $id, string $label, array $properties) {
    // Ensure the properties array contains the required keys.
    if (!isset($properties['url'], $properties['referenceUrl'], $properties['profile_id'])) {
      $this->logger->error('Missing required properties for creating a backstop scenario.');
      return FALSE;
    }
    $properties['id'] = $id;
    $properties['label'] = $label;
    try {

      if ($this->scenarioExists($properties)) {
        $url = parse_url($properties['url']);
        if (isset($url['path'])) {
          $this->messenger->addStatus(
            "A Backstop Scenario for URL {$url['path']} was skipped because it already exists."
          );
          return FALSE;
        }
      }

      // Create a new entity instance.
      $scenario = BackstopScenario::create($properties);
      $scenario->save();

      return $scenario;
    }
    catch (EntityStorageException $e) {
      $this->logger->error(
        'Failed to save backstop scenario entity: @message',
        ['@message' => $e->getMessage()]
      );
      return FALSE;
    }
  }

  /**
   * Remove scenarios associated with the given profile ID.
   *
   * @param string $profile_id
   *   The profile ID to remove scenarios for.
   *
   * @return void
   *   No return value.
   */
  public function removeScenarios(string $profile_id) {
    // Remove scenarios associated with the given profile ID.
    $scenarios = $this->configFactory
      ->listAll("backstop_generator.scenario.{$profile_id}_");
    foreach ($scenarios as $scenario) {
      $this->configFactory->getEditable($scenario)->delete();
    }
  }

  /**
   * Returns the path from a URL.
   *
   * @param string $url
   *   The URL to extract the path from.
   * @param string $langcode
   *   The language code to prepend to the path.
   * @param bool $remove_query_strings
   *   Whether to remove query strings from the URL.
   *
   * @return string
   *   The extracted path.
   */
  private function extractPath(string $url, string $langcode, bool $remove_query_strings = FALSE): string {
    $default_lang_id = $this->languageManager->getDefaultLanguage()->getId();
    $langcode = $langcode == $default_lang_id ? '' : "/$langcode";
    // If $url is a full URL, return only the path.
    if (parse_url($url, PHP_URL_SCHEME)) {
      $path = parse_url($url, PHP_URL_PATH);
      return $langcode . $path ?: "$langcode/";
    }
    // Assume it's already a path.
    if ($remove_query_strings) {
      // Remove the query strings.
      $clean_path = strtok($url, '?#');
      return $clean_path;
    }
    return $langcode . $url;
  }

  /**
   * Clean the path to remove characters not allowed in a config entity name.
   *
   * @param string $path
   *   The path to clean.
   *
   * @return string
   *   The cleaned path.
   */
  private function cleanPath(string $path): string {
    return preg_replace('/[:?*<>"\'\/\\\\]/', '', basename($path));
  }

  /**
   * Check if a scenario already exists based on the given properties.
   *
   * @param array $properties
   *   The properties to check for an existing scenario.
   *
   * @return bool
   *   TRUE if the scenario exists, FALSE otherwise.
   */
  private function scenarioExists(array $properties): bool {
    $existing = $this->entityTypeManager
      ->getStorage('backstop_scenario')
      ->loadByProperties([
        'profile_id' => $properties['profile_id'],
        'url' => $properties['url'],
      ]);
    return $existing ? TRUE : FALSE;
  }

}
