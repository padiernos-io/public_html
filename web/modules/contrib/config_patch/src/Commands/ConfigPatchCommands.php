<?php

namespace Drupal\config_patch\Commands;

use Drupal\Component\Render\MarkupInterface;
use Drupal\config_patch\ConfigCompare;
use Drupal\config_patch\Plugin\config_patch\output\CliOutputPluginInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Helper\Table;

/**
 * Drush commands for Config Patch.
 *
 * @package Drupal\config_patch\Commands
 */
class ConfigPatchCommands extends DrushCommands {

  /**
   * The comparer service.
   *
   * @var \Drupal\config_patch\ConfigCompare
   */
  protected $configCompare;

  /**
   * ConfigPatchCommands constructor.
   *
   * @param \Drupal\config_patch\ConfigCompare $configCompare
   *   Comparer service.
   */
  public function __construct(ConfigCompare $configCompare) {
    $this->configCompare = $configCompare;
  }

  /**
   * Create a configuration patch.
   *
   * @param string $plugin_id ID of the output plugin.
   *
   * @description Generate a patch from active to sync config.
   * @option filename Filename where to output the patch.
   * @option collections A comma separated list of included collections.
   *
   * @command config:patch
   * @aliases cpatch
   */
  public function outputPatch($plugin_id,
      $options = ['filename' => '', 'collections' => '']) {
    $this->configCompare->setOutputPlugin($plugin_id);
    $change_list = $this->configCompare->getChangelist();
    $patches = $this->configCompare->collectPatches();
    if (!empty($options['collections'])) {
      $collections = explode(',', $options['collections']);
      foreach ($patches as $collection_name => $collection) {
        if (!in_array($collection_name, $collections)) {
          unset($patches[$collection_name]);
        }
      }
    }
    if ($this->configCompare->getOutputPlugin() instanceof CliOutputPluginInterface) {
      $output = $this->configCompare->getOutputPlugin()
        ->outputCli($patches, $change_list, $options);
      if (!empty($options['filename'])) {
        file_put_contents($options['filename'], $output);
      }
      else {
        if ($output instanceof MarkupInterface) {
          $output = $output->__toString();
        }
        $this->output()->writeln($output);
      }
    }
    else {
      $this->output()->writeln('This plugin does not support cli output');
    }
  }

  /**
   * Output diff list.
   *
   * @description Generate a patch from active to sync config.
   * @option compact Simple output.
   *
   * @command config:patch:list
   */
  public function patchList($options = ['compact' => FALSE]) {
    $change_list = $this->configCompare->getChangelist();
    $table = new Table($this->output());
    if (empty($change_list)) {
      throw new \Exception('No changes found.');
    }
    if ($options['compact']) {
      $table->setStyle('compact');
    }
    else {
      $table->setHeaders(['config name', 'change type']);
    }
    foreach ($change_list as $collection_name => $collection) {
      foreach ($collection as $change_id => $change) {
        $table->addRow([$change['name'], $change['type']]);
      }
    }
    $this->output()->writeln($table->render());
  }

}
