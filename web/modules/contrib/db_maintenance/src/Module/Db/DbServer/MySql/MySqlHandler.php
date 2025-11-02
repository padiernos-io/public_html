<?php

/**
 * @file
 * MySqlHandler class.
 */

namespace Drupal\db_maintenance\Module\Db\DbServer\MySql;

use Drupal\db_maintenance\Module\Db\DbServer\DbServerHandlerInterface;

/**
 * MySqlHandler class.
 */
class MySqlHandler implements DbServerHandlerInterface {

  /**
   * Returns list of tables in the active database.
   */
  public function listTables() {
    $result = \Drupal::database()->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'",
      array(), array('fetch' => \PDO::FETCH_ASSOC));

    return $result;
  }

  /**
   * Optimizes table in the active database.
   */
  public function optimizeTable($table_name) {
    try {
      \Drupal::database()->query("OPTIMIZE TABLE {$table_name}")->execute();
    }
    catch (\Exception $e) {
      \Drupal\Component\Utility\DeprecationHelper::backwardsCompatibleCall(\Drupal::VERSION, '10.1.0', fn() => \Drupal\Core\Utility\Error::logException(\Drupal::logger('type'), $e), fn() => watchdog_exception('type', $e));
    }
  }

}
