<?php

namespace Drupal\minifyjs;

/**
 * Minify JS Interface.
 */
interface MinifyJsInterface {

  /**
   * Cache CID.
   *
   * @var string
   */
  const MINIFYJS_CACHE_CID = 'minifyjs:all_files';

  /**
   * Minify a single file.
   *
   * @param object $file
   *   The file to minify.
   */
  public function minify($file);

  /**
   * Remove the minified version of a single file (restore it).
   *
   * @param object $file
   *   The file to restore.
   */
  public function restore($file);

  /**
   * Scan for files.
   *
   * Recursively scan the entire doc tree looking for JS files, ignoring based
   * on the exclusion list.
   */
  public function scan();

  /**
   * Load all files.
   *
   * Load all of the minifyjs_file records from cache or directly from the
   * database.
   *
   * @return array
   *   The list of files.
   */
  public function loadAllFiles();

  /**
   * Minify File.
   *
   * Helper function that sends the JS off to be minified, handles the response,
   * stores the file in the filesystem and stores the file info in the managed
   * file tables.
   *
   * @param int $fid
   *   The file ID of the file to minify.
   * @param bool $reset
   *   Reset the cache or not.
   *
   * @return mixed
   *   Success of a translated string.
   */
  public function minifyFile($fid, $reset = FALSE);

  /**
   * Remove minified file.
   *
   * Helper function removes the file, the entry in the file_managed table and
   * sets the file status as unminified.
   *
   * @param int $fid
   *   The file id of the file remove.
   * @param bool $reset
   *   Reset the cache or not.
   *
   * @return mixed
   *   Success of a translated string.
   */
  public function removeMinifiedFile($fid, $reset = FALSE);

}
