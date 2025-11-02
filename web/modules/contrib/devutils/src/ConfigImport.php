<?php

namespace Drupal\devutils;

use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigException;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Import configs from yml files.
 */
class ConfigImport {
  use LoggerAwareTrait;
  use AutowireTrait;

  /**
   * Constructs an Import object.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   *   The configuration manager.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   The config storage.
   * @param \Drupal\Core\Cache\CacheBackendInterface $configCache
   *   The config cache.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher used to notify subscribers of config import events.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend to ensure multiple imports do not occur at the same
   *   time.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $configTyped
   *   The typed configuration manager.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   *   The module installer.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   *   The theme handler.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Extension\ThemeExtensionList $extensionListTheme
   *   The theme extension list.
   */
  public function __construct(
    #[Autowire(service: 'config.manager')]
    protected ConfigManagerInterface $configManager,
    #[Autowire(service: 'config.storage')]
    protected StorageInterface $configStorage,
    #[Autowire(service: 'cache.config')]
    protected CacheBackendInterface $configCache,
    #[Autowire(service: 'module_handler')]
    protected ModuleHandlerInterface $moduleHandler,
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
    #[Autowire(service: 'lock')]
    protected LockBackendInterface $lock,
    #[Autowire(service: 'config.typed')]
    protected TypedConfigManagerInterface $configTyped,
    #[Autowire(service: 'module_installer')]
    protected ModuleInstallerInterface $moduleInstaller,
    #[Autowire(service: 'theme_handler')]
    protected ThemeHandlerInterface $themeHandler,
    #[Autowire(service: 'string_translation')]
    protected TranslationInterface $stringTranslation,
    #[Autowire(service: 'extension.list.module')]
    protected ModuleExtensionList $moduleExtensionList,
    #[Autowire(service: 'logger.factory')]
    protected LoggerChannelFactoryInterface $logger_factory,
    protected ThemeExtensionList $extensionListTheme
  ) {
    $this->logger = $logger_factory->get('devutils');
  }

  /**
   * The replacement storage object.
   *
   * @param string $module
   *   The module name.
   * @param \Drupal\Core\Config\StorageInterface $active_storage
   *   The active storage object.
   * @param array $configs
   *   The list of configs.
   *
   * @return \Drupal\config\StorageReplaceDataWrapper
   *   The storage replace data wrapper.
   */
  private function getReplacementStorage(string $module, StorageInterface $active_storage, array $configs = []): StorageReplaceDataWrapper {
    // Determine source directory.
    $source_storage_dir = $this->moduleExtensionList->getPath($module);

    $configInstallDirectory = new FileStorage(
      $source_storage_dir . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY
    );
    $configOptionalDirectory = new FileStorage(
      $source_storage_dir . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY
    );

    $replacement_storage = new StorageReplaceDataWrapper($active_storage);

    if (empty($configs)) {
      $configs = array_merge(
        $configInstallDirectory->listAll(), $configOptionalDirectory->listAll()
      );
    }

    foreach ($configs as $config) {
      $data = FALSE;
      if ($configInstallDirectory->exists($config)) {
        $data = $configInstallDirectory->read($config);
      }
      elseif ($configOptionalDirectory->exists($config)) {
        $data = $configOptionalDirectory->read($config);
      }
      if ($data) {
        $replacement_storage->replaceData($config, $data);
      }
    }

    return $replacement_storage;
  }

  /**
   * Imports configuration for the given module.
   *
   * @param string $module
   *   The module name.
   * @param array $configs
   *   The list of configs.
   *
   * @throws \Exception
   */
  public function import(string $module, array $configs = []): void {
    // Determine $source_storage in partial case.
    $active_storage = $this->configStorage;
    $replacement_storage = $this->getReplacementStorage(
      $module, $active_storage, $configs
    );
    $source_storage = $replacement_storage;
    $storage_comparer = new StorageComparer($source_storage, $active_storage);

    if (!$storage_comparer->createChangelist()->hasChanges()) {
      $this->logger()->notice(('There are no changes to import.'));
      return;
    }

    $config_importer = new ConfigImporter(
      $storage_comparer,
      $this->eventDispatcher,
      $this->configManager,
      $this->lock,
      $this->configTyped,
      $this->moduleHandler,
      $this->moduleInstaller,
      $this->themeHandler,
      $this->stringTranslation,
      $this->moduleExtensionList,
      $this->extensionListTheme
    );

    try {
      // This is the contents of \Drupal\Core\Config\ConfigImporter::import.
      // Copied here so we can log progress.
      if ($config_importer->hasUnprocessedConfigurationChanges()) {
        $sync_steps = $config_importer->initialize();
        foreach ($sync_steps as $step) {
          $context = [];
          do {
            $config_importer->doSyncStep($step, $context);
            if (isset($context['message'])) {
              $this->logger()
                ->notice(
                  str_replace(
                    'Synchronizing', 'Synchronized',
                    (string) $context['message']
                  )
                );
            }
          } while ($context['finished'] < 1);
        }
        // Clear the cache of the active config storage.
        $this->configCache->deleteAll();
      }
      if ($config_importer->getErrors()) {
        throw new ConfigException('Errors occurred during import');
      }
      else {
        $this->logger()->info('The configuration was imported successfully.');
      }
    }
    catch (ConfigException $e) {
      // Return a negative result for UI purposes. We do not differentiate
      // between an actual synchronization error and a failed lock, because
      // concurrent synchronizations are an edge-case happening only when
      // multiple developers or site builders attempt to do it without
      // coordinating.
      $message = 'The import failed due to the following reasons:' . "\n";
      $message .= implode("\n", $config_importer->getErrors());

      $this->logger()->error($message);
      throw new \Exception($message);
    }
  }

  /**
   * Gets the logger.
   *
   * This function gets the logger. The logger is used to log messages to the
   * Drupal log.
   *
   * @return \Drupal\Core\Logger\LoggerChannelInterface
   *   The logger.
   */
  protected function logger(): LoggerChannelInterface {
    return $this->logger;
  }

}
