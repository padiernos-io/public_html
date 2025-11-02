<?php

namespace Drupal\media_thumbnails_epub\Plugin\MediaThumbnail;

use Drupal\media_thumbnails\Plugin\MediaThumbnailBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Media thumbnail plugin for epub documents.
 *
 * @MediaThumbnail(
 *   id = "media_thumbnail_epub",
 *   label = @Translation("Media Thumbnail EPUB"),
 *   mime = {
 *     "application/epub+zip",
 *   }
 * )
 */
class MediaThumbnailEPUB extends MediaThumbnailBase {

  /**
   * The archiver manager service.
   *
   * @var \Drupal\Core\Archiver\ArchiverInterface
   */
  protected $archiver;

  /**
   * The file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): MediaThumbnailBase {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->archiver = $container->get('plugin.manager.archiver');
    $instance->fileRepository = $container->get('file.repository');
    return $instance;
  }

  /**
   * Creates a managed thumbnail file using the passed source file uri.
   *
   * {@inheritdoc}
   */
  public function createThumbnail($sourceUri) {

    // Check the required php extension.
    if (!extension_loaded('imagick')) {
      $this->logger->warning($this->t('Imagick php extension not loaded.'));
      return NULL;
    }

    // Get the real path of the passed epub file.
    $path = $this->fileSystem->realpath($sourceUri);
    if (!$path) {
      $this->logger->warning($this->t('Media entity source file (epub) not found'));
      return NULL;
    }

    // Create an unmanaged copy with extension 'zip'.
    $path = $this->fileSystem->copy($path, str_replace('.epub', '.zip', $path));

    // We'll need a unique folder name for the unzipped file.
    $hash = hash_file('md5', $path);

    // Create a zip instance.
    $zip = $this->archiver->getInstance(['filepath' => $path]);

    // Extract the file and delete the zip file.
    $target_folder = 'temporary://' . $hash;
    $zip->extract($target_folder);
    $this->fileSystem->delete($path);

    // The container file has a fixed location, grab it.
    $container = simplexml_load_string(file_get_contents($target_folder . '/META-INF/container.xml'));
    if (!$container) {
      $this->logger->warning($this->t('Could not open epub container file.'));
      return NULL;
    }
    $container = json_decode(json_encode($container), TRUE);
    if (!$container) {
      $this->logger->warning($this->t('Could not decode epub container file.'));
      return NULL;
    }

    // Next step: fetch the manifest file, using the container information.
    $manifest_path = $container['rootfiles']['rootfile']['@attributes']['full-path'];
    $manifest = simplexml_load_string(file_get_contents($target_folder . '/' . $manifest_path));
    if (!$manifest) {
      $this->logger->warning($this->t('Could not open epub manifest file.'));
      return NULL;
    }
    $manifest = json_decode(json_encode($manifest), TRUE);
    if (!$manifest) {
      $this->logger->warning($this->t('Could not decode epub manifest file.'));
      return NULL;
    }

    // Get the file name of the cover image, if any.
    if (!$cover = $this->getCoverFilename($manifest)) {
      $this->logger->notice($this->t('EPUB cover image not found in source file.'));
      return NULL;
    }

    // The cover image path is relative to the content base folder.
    $folders = explode('/', $manifest_path);
    array_pop($folders);
    $base_path = implode('/', $folders);
    $cover_path = $target_folder . '/' . $base_path . '/' . $cover;

    // Get the cover image.
    $im = new \Imagick();
    try {
      $im->readImage($cover_path);
    }
    catch (\ImagickException $e) {
      $this->logger->warning($e->getMessage());
      return NULL;
    }

    // Resize the thumbnail to the globally configured width.
    $width = $this->configuration['width'] ?? 500;
    if ($im->getImageWidth() > $width) {
      try {
        $im->scaleImage($width, 0);
      }
      catch (\ImagickException $e) {
        $this->logger->warning($e->getMessage());
        return NULL;
      }
    }

    // Get the file extension using the mime type of the cover image.
    $mime = $im->getImageMimeType();
    $parts = explode('/', $mime);
    $extension = array_pop($parts);

    // Convert svg thumbnails to png.
    if ($mime === 'image/svg+xml') {
      $im->setImageFormat('png');
      $extension = 'png';
    }
    // Create the file object.
    $image = $im->getImageBlob();
    $im->clear();
    $im->destroy();
    return $this->fileRepository->writeData($image, $sourceUri . '.' . $extension);

  }

  /**
   * Get the file name of the cover image file.
   *
   * @param array $manifest
   *   The manifest data.
   *
   * @return string|null
   *   The file name of the cover image.
   */
  public function getCoverFilename(array $manifest) {
    // Try to get the filename using the meta attribute.
    $meta_data = $manifest['metadata']['meta'];
    if (!isset($meta_data[0])) {
      $meta_data[0] = $meta_data;
    }
    foreach ($meta_data as $meta) {
      if (isset($meta['@attributes']['name']) && $meta['@attributes']['name'] === 'cover') {
        $attribute = $meta['@attributes']['content'];
        foreach ($manifest['manifest']['item'] as $item) {
          if ($item['@attributes']['id'] === $attribute) {
            return ($item['@attributes']['href']);
          }
        }
      }
    }
    // Look for an item with id "cover" or property "cover-image".
    foreach ($manifest['manifest']['item'] as $item) {
      if (isset($item['@attributes']['id']) && ($item['@attributes']['id'] === 'cover' || $item['@attributes']['id'] === 'cover-image')) {
        return ($item['@attributes']['href']);
      }
      if (isset($item['@attributes']['properties']) && $item['@attributes']['properties'] === 'cover-image') {
        return ($item['@attributes']['href']);
      }
    }
    return NULL;
  }

}
