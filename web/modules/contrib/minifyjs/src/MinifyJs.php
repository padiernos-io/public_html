<?php

namespace Drupal\minifyjs;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\ProxyClass\File\MimeType\MimeTypeGuesser;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use MatthiasMullie\Minify\JS;

/**
 * Minify JS Service.
 */
class MinifyJs implements MinifyJsInterface {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $cache;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $config;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $currentUser;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The file_system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The file.usage service.
   *
   * @var \Drupal\file\FileUsage\DatabaseFileUsageBackend
   */
  protected DatabaseFileUsageBackend $fileUsage;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The mime type guesser service.
   *
   * @var \Drupal\Core\File\MimeType\MimeTypeGuesser
   */
  protected MimeTypeGuesser $mimeTypeGuesser;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected PathMatcherInterface $pathMatcher;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\File\MimeType\MimeTypeGuesser $mime_type_guesser
   *   The file.mime_type.guesser service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file_system service.
   * @param \Drupal\file\FileUsage\DatabaseFileUsageBackend $file_usage
   *   The file.usage service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path.matcher service.
   */
  public function __construct(
    CacheBackendInterface $cache,
    ConfigFactoryInterface $config,
    AccountProxyInterface $current_user,
    Connection $database,
    TimeInterface $time,
    EntityTypeManagerInterface $entity_type_manager,
    MimeTypeGuesser $mime_type_guesser,
    FileSystemInterface $file_system,
    DatabaseFileUsageBackend $file_usage,
    MessengerInterface $messenger,
    PathMatcherInterface $path_matcher,
  ) {
    $this->cache = $cache;
    $this->config = $config;
    $this->currentUser = $current_user;
    $this->database = $database;
    $this->time = $time;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->fileUsage = $file_usage;
    $this->messenger = $messenger;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->pathMatcher = $path_matcher;
  }

  /**
   * {@inheritdoc}
   */
  public function minify($file) {
    $result = $this->minifyFile($file, TRUE);

    if ($result === TRUE) {
      $this->messenger->addMessage(new TranslatableMarkup('File was minified successfully.'));
    }
    else {
      $this->messenger->addMessage($result, 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function restore($file) {
    $result = $this->removeMinifiedFile($file, TRUE);

    if ($result === TRUE) {
      $this->messenger->addMessage(new TranslatableMarkup('File was restored successfully.'));
    }
    else {
      $this->messenger->addMessage($result, 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function scan() {

    // Recursive scan of the entire doc root to find .js files. Include
    // minified files as well so they can be re-minified (comments removed).
    $directory = new \RecursiveDirectoryIterator(DRUPAL_ROOT);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/\.js$/i');

    // Process files.
    $new_files = [];
    $old_files = [];
    $changed_files = [];
    $existing = $this->loadAllFiles();
    $exclusions = $this->config->get('minifyjs.config')->get('exclusion_list');

    foreach ($regex as $info) {
      $new_absolute = $info->getPathname();
      $new_relative = str_replace(DRUPAL_ROOT . DIRECTORY_SEPARATOR, '', $new_absolute);

      // Skip exclusions.
      if ($this->pathMatcher->matchPath($new_relative, $exclusions)) {
        continue;
      }

      // Loop existing and see if it already exists from previous scans.
      $exists = FALSE;
      foreach ($existing as $file) {
        if ($file->uri == $new_relative) {

          // See if the size and modified time differ from the last time the
          // scan checked this file. If the file has changed (based on those
          // two pieces of data), mark the minified version for removal if a
          // minified version of the file exists.
          if (!empty($file->minified_uri)) {
            $size = filesize($new_absolute);
            $modified = filemtime($new_absolute);
            if ($size != $file->size || $modified != $file->modified) {
              $changed_files[$new_relative] = $file;
            }
          }
          $exists = TRUE;
          $old_files[$new_relative] = TRUE;
          break;
        }
      }

      // File not found in the existing array, so it's new.
      if (!$exists) {
        $new_files[$new_absolute] = TRUE;
      }
    }

    // Build a list of files that currently exist in the minifyjs_file table but
    // no longer exist in the file system. These files should be removed.
    foreach ($existing as $file) {
      if (!isset($old_files[$file->uri])) {
        $this->removeFile($file->uri);
      }
    }

    // Remove changed files.
    foreach ($changed_files as $file_uri => $file) {
      $this->removeFile($file->uri);
      $new_files[$file_uri] = TRUE;
      $this->messenger->addMessage(
        new TranslatableMarkup(
          'Original file %file has been modified and was restored.',
          ['%file' => $file_uri]
        )
      );
    }

    // Add all new files to the database.
    foreach ($new_files as $file => $junk) {
      $this->database->insert('minifyjs_file')
        ->fields(
          [
            'uri' => str_replace(DRUPAL_ROOT . DIRECTORY_SEPARATOR, '', $file),
            'size' => filesize($file),
            'modified' => filemtime($file),
          ]
        )
        ->execute();
    }

    // Clear the cache so all of these new files will be picked up.
    $this->cache->delete(MinifyJsInterface::MINIFYJS_CACHE_CID);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllFiles() {

    // Load files from cache.
    if ($cache = $this->cache->get(MinifyJsInterface::MINIFYJS_CACHE_CID)) {
      return $cache->data;
    }

    // Re-build cache.
    $result = $this->database->select('minifyjs_file', 'f')
      ->fields('f')
      ->orderBy('uri')
      ->execute();

    $exclusions = $this->config->get('minifyjs.config')->get('exclusion_list');

    $files = [];
    while ($file = $result->fetchObject()) {

      // Ignore the exclusions.
      if (!$this->pathMatcher->matchPath($file->uri, $exclusions)) {
        $files[$file->fid] = $file;
      }
    }

    // Cache for 1 day.
    $this->cache->set(MinifyJsInterface::MINIFYJS_CACHE_CID, $files, strtotime('+1 day', $this->time->getRequestTime()));

    return $files;
  }

  /**
   * {@inheritdoc}
   */
  public function minifyFile($fid, $reset = FALSE) {

    // Load the file by fid.
    $files = $this->loadAllFiles();
    $file = $files[$fid];
    $js = file_get_contents(DRUPAL_ROOT . DIRECTORY_SEPARATOR . $file->uri);

    // Minify the JS, if it has a length. 0 byte files should pass by the
    // minification process.
    $minified = $js;
    if (strlen($js)) {
      $minifier = new JS(DRUPAL_ROOT . DIRECTORY_SEPARATOR . $file->uri);
      $minified = $minifier->execute();
    }

    // Create the directory tree if it doesn't exist.
    $minifyjs_folder = 'public://minifyjs/' . dirname($file->uri);
    $this->fileSystem->prepareDirectory($minifyjs_folder, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Save the file first to the temp folder and then copy to the
    // public filesystem.
    $file_name = str_replace('.js', '.min.js', basename($file->uri));
    $tmp_file = $this->fileSystem->getTempDirectory() . DIRECTORY_SEPARATOR . $file_name;
    $file_uri = $minifyjs_folder . DIRECTORY_SEPARATOR . $file_name;
    if (file_put_contents($tmp_file, $minified) !== FALSE) {
      if (copy($tmp_file, $file_uri)) {

        // Save the file in the managed file table.
        if (empty($file->minified_uri)) {
          $file = $this->entityTypeManager->getStorage('file')
            ->create(
              [
                'uid' => $this->currentUser->id(),
                'uri' => $file_uri,
                'filename' => $file_name,
                'filemime' => $this->mimeTypeGuesser->guessMimeType($file->uri),
                'status' => FileInterface::STATUS_PERMANENT,
              ]
            );
          $file->save();
          $this->fileUsage->add($file, 'minifyjs', 'node', 1);
        }

        $filesize = filesize($file_uri);

        // Update the minifyjs table.
        $this->database->update('minifyjs_file')
          ->fields(
            [
              'minified_uri' => $file_uri,
              'minified_size' => ($filesize) ? $filesize : 0,
              'minified_modified' => $this->time->getRequestTime(),
            ]
          )
          ->condition('fid', $fid)
          ->execute();

        // Clean up temp folder.
        unlink($tmp_file);

        // Clear the cache so this change will be reflected in
        // loadAllFiles().
        if ($reset) {
          $this->cache->delete(MinifyJsInterface::MINIFYJS_CACHE_CID);
        }

        return TRUE;
      }
      else {
        return new TranslatableMarkup(
          'Could not copy the file from the %tmp folder.',
          ['%tmp' => $this->fileSystem->getTempDirectory()]
        );
      }
    }
    else {
      return new TranslatableMarkup(
        'Could not save the file - %file',
        ['%file' => $this->fileSystem->getTempDirectory() . DIRECTORY_SEPARATOR . $file_name]
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function removeMinifiedFile($fid, $reset = FALSE) {

    // Get minified uri from the minifyjs_file table.
    $query = $this->database->select('minifyjs_file', 'm')
      ->fields('m', ['minified_uri'])
      ->condition('m.fid', $fid);

    // Make sure that it exists.
    if ($query->countQuery()->execute()->fetchField() > 0) {
      $file = $query->execute()->fetchObject();

      // Get the fid of the minified table.
      $query = $this->database->select('file_managed', 'f')
        ->fields('f', ['fid'])
        ->condition('f.uri', $file->minified_uri);

      // Make sure that it exists.
      if ($query->countQuery()->execute()->fetchField() > 0) {
        $file = $query->execute()->fetchObject();

        // Remove the file from the file_managed table.
        $file = $this->entityTypeManager->getStorage('file')
          ->load($file->fid);
        $file->delete();

        // Set the file status to non-minified.
        $this->database->update('minifyjs_file')
          ->fields(
            [
              'minified_uri' => '',
              'minified_size' => 0,
              'minified_modified' => 0,
            ]
          )
          ->condition('fid', $fid)
          ->execute();

        // Clear the cache so this change will be reflected in
        // loadAllFiles().
        if ($reset) {
          $this->cache->delete(MinifyJsInterface::MINIFYJS_CACHE_CID);
        }

        return TRUE;
      }
    }

    return new TranslatableMarkup('File not found. Check that the file ID is correct.');
  }

  /**
   * Remove a file.
   *
   * Helper function removes the file, the entry in the file_managed table and
   * the entry in the minifyjs_file.
   *
   * @param string $file_uri
   *   The URI of the file to remove.
   *
   * @return bool
   *   The success of the operation.
   */
  protected function removeFile($file_uri) {

    // Get the fid and minified uri of the file.
    $query = $this->database->select('minifyjs_file', 'm')
      ->fields('m', ['fid', 'minified_uri'])
      ->condition('m.uri', $file_uri);

    // Make sure that it exists.
    if ($query->countQuery()->execute()->fetchField() > 0) {
      $file = $query->execute()->fetchObject();

      // Handle the minified file, if applicable.
      if (!empty($file->minified_uri)) {

        // Get the fid of the minified file.
        $query = $this->database->select('file_managed', 'f')
          ->fields('f', ['fid'])
          ->condition('f.uri', $file->minified_uri);
        if ($query->countQuery()->execute()->fetchField() > 0) {
          $minified_file = $query->execute()->fetchObject();

          // Remove the file from the file_managed table.
          $minified_file = $this->entityTypeManager->getStorage('file')
            ->load($minified_file->fid);
          $minified_file->delete();
        }
      }

      // Remove the file from minifyjs_file table.
      $this->database->delete('minifyjs_file')
        ->condition('fid', $file->fid)
        ->execute();

      return TRUE;
    }

    return FALSE;
  }

}
