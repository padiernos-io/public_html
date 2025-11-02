<?php

namespace Drupal\config_patch;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use Drupal\Core\Config\StorageCopyTrait;
use Drupal\Core\Config\MemoryStorage;

/**
 * Compare configuration sets.
 *
 * @package Drupal\config_patch
 */
class ConfigCompare {

  use StringTranslationTrait;
  use StorageCopyTrait;

  /**
   * The sync configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $syncStorage;

  /**
   * The export configuration object.
   *
   * See https://www.drupal.org/node/3037022.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $exportStorage;

  /**
   * The active configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $activeStorage;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Module configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The output plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $outputPluginManager;

  /**
   * Patch output plugin.
   *
   * @var \Drupal\config_patch\Plugin\config_patch\output\OutputPluginInterface
   */
  protected $outputPlugin;

  /**
   * Caching.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * ConfigCompare constructor.
   *
   * @param \Drupal\Core\Config\StorageInterface $sync_storage
   *   Sync storage.
   * @param \Drupal\Core\Config\StorageInterface $export_storage
   *   Export storage.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   Config manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   For module config.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $output_plugin_manager
   *   Manage patch output plugins.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Caching.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function __construct(StorageInterface $sync_storage, StorageInterface $export_storage, StorageInterface $active_storage, ConfigManagerInterface $config_manager, ConfigFactoryInterface $config_factory, PluginManagerInterface $output_plugin_manager, CacheBackendInterface $cacheBackend) {
    $this->syncStorage = $sync_storage;
    $this->exportStorage = $export_storage;
    $this->activeStorage = $active_storage;
    $this->configManager = $config_manager;
    $this->config = $config_factory->get('config_patch.settings');
    $this->outputPluginManager = $output_plugin_manager;
    $this->outputPlugin = $this->outputPluginManager->createInstance($this->config->get('output_plugin'));
    $this->cache = $cacheBackend;
  }

  /**
   * Gets the list form element key according to collection name.
   *
   * @param string $collection_name
   *   Collection name.
   *
   * @return string
   *   The key to be used in the form element.
   */
  public function getListKey($collection_name) {
    $list_key = 'list';
    if ($collection_name != StorageInterface::DEFAULT_COLLECTION) {
      $list_key .= '_' . preg_replace('/[^a-z0-9_]+/', '_', $collection_name);
    }
    return $list_key;
  }

  /**
   * Make the sync storage match the export storage for a particular
   *
   * @param string $item
   * @param string $collection
   */
  public function revert($item, $collection = StorageInterface::DEFAULT_COLLECTION) {
    if ($collection != StorageInterface::DEFAULT_COLLECTION) {
      $disk_storage = $this->syncStorage->createCollection($collection);
      $active_storage = $this->activeStorage->createCollection($collection);
    }
    else {
      $disk_storage = $this->syncStorage;
      $active_storage = $this->activeStorage;
    }

    $changes = $this->getChangelist();
    if ($change = $changes[$collection][$item] ?? NULL) {
      // Default operation is to pull from disk into active.
      $add = $change['name'];
      $remove = NULL;
      if ($change['type'] == 'rename') {
        $parsed = explode(' to ', $change['name']);
        $add = trim($parsed[0]);
        $remove = trim($parsed[0]);
      }
      if ($change['type'] == 'create') {
        $remove = $change['name'];
        $add = NULL;
      }

      if ($add) {
        $data = $disk_storage->read($add);
        $active_storage->write($add, $data);
      }
      if ($remove) {
        $active_storage->delete($remove);
      }
    }
  }

  /**
   * Get the text of the two configs.
   *
   * @param string $source_name
   *   Config source name.
   * @param string $target_name
   *   Config target name.
   * @param string $collection
   *   The config collection.
   *
   * @return array
   *   Tuple of source, target data.
   */
  protected function getTexts($source_name, $target_name = NULL, $collection = StorageInterface::DEFAULT_COLLECTION) {
    if ($collection != StorageInterface::DEFAULT_COLLECTION) {
      $source_storage = $this->syncStorage->createCollection($collection);
      $target_storage = $this->exportStorage->createCollection($collection);
    }
    else {
      $source_storage = $this->syncStorage;
      $target_storage = $this->exportStorage;
    }
    if (!isset($target_name)) {
      $target_name = $source_name;
    }

    // Output should show configuration object differences formatted as YAML.
    // But the configuration is not necessarily stored in files. Therefore, they
    // need to be read and parsed, and lastly, dumped into YAML strings.
    $raw_source = $source_storage->read($source_name);
    $source_data = $raw_source ? Yaml::encode($raw_source) : '';
    $raw_target = $target_storage->read($target_name);
    $target_data = $raw_target ? Yaml::encode($raw_target) : '';

    return [
      $source_data,
      $target_data,
    ];
  }

  /**
   * Run diffs, create a patch.
   *
   * @param string $source
   *   Source config.
   * @param string $target
   *   Target config.
   * @param string $from_file
   *   Name of the source file.
   * @param string $to_file
   *   Name of the target file.
   *
   * @return string|string[]|null
   *   The diff.
   */
  protected function diff($source, $target, $from_file, $to_file) {
    $builder = new StrictUnifiedDiffOutputBuilder([
      'collapseRanges' => FALSE,
      // Ranges of length one are rendered with the trailing `,1`.
      'commonLineThreshold' => 6,
      // Number of lines before ending a new hunk and creating a new one.
      'contextLines' => 3,
      'fromFile' => $from_file,
      'fromFileDate' => NULL,
      'toFile' => $to_file,
      'toFileDate' => NULL,
    ]);

    $differ = new Differ($builder);
    $patch = $differ->diff($source, $target);

    // Fix for create/delete file create header.
    $patch = preg_replace('/' . preg_quote('@@ -1,0') . '/s', '@@ -0,0', $patch);
    $patch = preg_replace('/' . preg_quote('+1,0 @@') . '/s', '+0,0 @@', $patch);

    return $patch;
  }

  /**
   * Get a git-style hash.
   *
   * @param string $text
   *   String to hash.
   *
   * @return false|string
   *   SHA-ish hash.
   */
  protected function getHash($text) {
    if (empty($text)) {
      return "0000000";
    }
    $sha = sha1($text);
    return substr($sha, 0, 7);
  }

  /**
   * Gets the changes in one array.
   *
   * @return array
   *   Changes to the configuration following the structure:
   *   [
   *     'collection_name' => [
   *       'configuration_name' => [
   *            'name' => 'configuration_name',
   *            'type' => 'type of change' (update, create, delete, rename)
   *         ]
   *     ]
   *   ]
   */
  public function getChangelist() {
    $cached_changes = $this->cache->get('config_patch_changes');
    if (!empty($cached_changes)) {
      $changes = $cached_changes->data;
    }
    else {
      $changes = [];
      $storage_comparer = new StorageComparer($this->exportStorage, $this->syncStorage, $this->configManager);

      if ($storage_comparer->createChangelist()->hasChanges()) {
        $collections = $storage_comparer->getAllCollectionNames();
        foreach ($collections as $collection) {
          foreach ($storage_comparer->getChangelist(NULL, $collection) as $config_change_type => $config_names) {
            if (empty($config_names)) {
              continue;
            }
            foreach ($config_names as $config_name) {
              if ($config_change_type == 'rename') {
                $names = $storage_comparer->extractRenameNames($config_name);
                $config_name = sprintf('%s to %s', $names['old_name'], $names['new_name']);
              }
              $changes[$collection][$config_name] = [
                'name' => $config_name,
                'type' => $config_change_type,
              ];
            }
          }
        }
      }
      $this->cache->set('config_patch_changes', $changes, Cache::PERMANENT, ['config_patch']);
    }
    return $changes;
  }

  /**
   * Collects the patches for selected config items.
   *
   * @param array $list_to_export
   *   The list of the configuration items to export.
   *   The array is two-dimensional: first level is collection name, second
   *   level the list of the config items. Example:
   *     $list_to_export = [
   *       '' => [
   *           'core.extensions.yml',
   *           'site.settings.yml',
   *        ]
   *     ]
   *   If array is empty all changes will be exported.
   *
   * @return array
   *   Array of the patches per file.
   */
  public function collectPatches(array $list_to_export = []) {
    $changes = $this->getChangelist();
    $collection_patches = [];

    foreach ($changes as $collection_name => $collection) {
      $list = !empty($list_to_export[$collection_name]) ? $list_to_export[$collection_name] : array_keys($collection);

      foreach (array_filter($list) as $config_name) {
        $config_change_type = $changes[$collection_name][$config_name]['type'];

        $source_name = $config_name;
        $target_name = $config_name;
        if ($config_change_type == 'rename') {
          $names = $storage_comparer->extractRenameNames($config_name);
          $source_name = $names['old_name'];
          $target_name = $names['new_name'];
        }

        list($source, $target) = $this->getTexts($source_name, $target_name, $collection_name);

        $base_dir = trim($this->config->get('config_base_path') ?? '', '/');
        if ($collection_name != StorageInterface::DEFAULT_COLLECTION) {
          $base_dir .= '/' . str_replace('.', '/', $collection_name);
        }
        $from_file = 'a/' . ($base_dir ? $base_dir . '/' : '') . $source_name . '.yml';
        $to_file = 'b/' . ($base_dir ? $base_dir . '/' : '') . $target_name . '.yml';

        $diff_header = "diff --git " . $from_file . " " . $to_file;
        $index_header = "index " . $this->getHash($source) . ".." . $this->getHash($target) . " 100644";

        if ($config_change_type == 'create') {
          $from_file = '/dev/null';
          $index_header = "new file mode 100644\n" . $index_header;
        }
        if ($config_change_type == 'delete') {
          $to_file = '/dev/null';
          $index_header = "deleted file mode 100644\n" . $index_header;
        }

        $formatted = $this->diff($source, $target, $from_file, $to_file);

        // Add a diff header.
        $formatted = $diff_header . "\n" . $index_header . "\n" . $formatted;

        $patch_key = empty($collection_name) ? 'default' : $collection_name;
        $collection_patches[$patch_key][$config_name] = $formatted;
      }
    }
    return $collection_patches;
  }

  /**
   * Returns the list of output plugin definitions.
   *
   * @return mixed[]
   *   The plugins available.
   */
  public function getOutputPlugins() {
    return $this->outputPluginManager->getDefinitions();
  }

  /**
   * Retrieve the selected output plugin.
   */
  public function getOutputPlugin() {
    return $this->outputPlugin;
  }

  /**
   * Sets output plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function setOutputPlugin($plugin_id) {
    $this->outputPlugin = $this->outputPluginManager->createInstance($plugin_id);
  }

}
