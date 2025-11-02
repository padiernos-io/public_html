<?php

namespace Drupal\module_cleanup\Commands;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Database\Connection;
use Drupal\Core\Field\FieldException;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldStorageConfig;
use Drush\Commands\DrushCommands;

/**
 * Defines Drush commands for the Feeds module.
 */
class ModuleCleanupDrushCommands extends DrushCommands {

  use StringTranslationTrait;

  const EXIT_ERROR = 1;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new ModuleCleanupDrushCommands object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
    parent::__construct();
  }

  /**
   * Runs field_purge_batch.
   *
   * @command modcup:field-purge-batch
   * @aliases modcup-fpb
   * @usage modcup-fpb
   */
  public function runFieldPurgeBatch() {
    try {
      field_purge_batch(1000);
      $this->logger()->success($this->t("field_purge_batch(1000) ran successfully."));
    }
    catch (PluginNotFoundException $e) {
      $this->logger()->error($this->t('@error', ['@error' => $e->getMessage()]));
    }
    catch (FieldException $e) {
      $this->logger()->error($this->t('@error', ['@error' => $e->getMessage()]));
    }
  }

  /**
   * Create field storage by field name and entity type.
   *
   * @param string $field_name
   *   The field name of the entity.
   * @param string $entity_type
   *   The entity type.
   *
   * @command modcup:create-storage
   * @aliases modcup-cs
   * @usage modcup-cs field_name entity_type
   */
  public function createStorage($field_name, $entity_type) {
    if (empty($field_name)) {
      $this->logger()->error($this->t('Please specify the field name of the entity.'));
      return self::EXIT_ERROR;
    }
    if (empty($entity_type)) {
      $this->logger()->error($this->t('Please specify the entity type.'));
      return self::EXIT_ERROR;
    }

    // Create field storage.
    if (FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'string',
    ])->save()) {
      $this->logger()->success($this->t('The field storage was created successfully.'));
    }
    else {
      $this->logger()->error($this->t('No field storage was created.'));
      return self::EXIT_ERROR;
    }
  }

  /**
   * Deletes a field left over when a module was uninstalled.
   *
   * @param string $field_name
   *   The field name of the entity.
   * @param string $entity_type
   *   The entity type.
   *
   * @command modcup:delete-field
   * @aliases modcup-df
   * @usage modcup-df field_name entity_type
   */
  public function deleteField($field_name, $entity_type) {
    if (empty($field_name)) {
      $this->logger()->error($this->t('Please specify the field name of the entity.'));
      return self::EXIT_ERROR;
    }
    if (empty($entity_type)) {
      $this->logger()->error($this->t('Please specify the entity type.'));
      return self::EXIT_ERROR;
    }

    if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
      // Create field storage.
      if (FieldStorageConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'type' => 'string',
      ])->save()) {
        $this->logger()->success($this->t('The field storage was created successfully.'));
      }
      else {
        $this->logger()->error($this->t('No field storage was created.'));
        return self::EXIT_ERROR;
      }
    }

    field_purge_batch(1000);
    if (FieldStorageConfig::loadByName($entity_type, $field_name)) {
      // Create field storage.
      FieldStorageConfig::loadByName($entity_type, $field_name)->delete();
      $this->logger()->success($this->t('The field storage was deleted successfully.'));
    }
  }

  /**
   * Cleans up No available releases found errors in Available updates.
   *
   * @command modcup:clear-updates
   * @aliases modcup-cu
   * @usage modcup-cu
   */
  public function clearUpdates() {
    // Clears errors in Available updates.
    if ($this->database->delete('key_value')->condition('collection', 'update_fetch_task')->execute()) {
      $this->logger()->success($this->t('Avalable updates restored.'));
    }
  }

  /**
   * Deletes data from a module left over in the config table.
   *
   * @param string $module
   *   The module name of the entity.
   *
   * @command modcup:delete-config
   * @aliases modcup-dc
   * @usage modcup-dc module_name
   */
  public function deleteConfig($module) {
    if (empty($module)) {
      $this->logger()->error($this->t('Please specify the module name of the entity.'));
      return self::EXIT_ERROR;
    }

    // Deletes module config data.
    if ($this->database->delete('key_value')->condition('name', $module)->execute()) {
      $this->logger()->success($this->t("%module transient data deleted.", ['%module' => $this->createName($module)]));
    }
    else {
      $this->logger()->error($this->t('No data was deleted.'));
      return self::EXIT_ERROR;
    }
  }

  /**
   * Create a capitalizes name from machine name.
   *
   * @param string $machine_name
   *   The machine name.
   */
  private function createName($machine_name) {
    return ucfirst(implode(" ", explode("_", $machine_name)));
  }

}
