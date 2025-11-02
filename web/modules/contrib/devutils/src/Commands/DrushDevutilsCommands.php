<?php

namespace Drupal\devutils\Commands;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush commandfile for devutils module.
 */
class DrushDevutilsCommands extends DrushCommands {
  use AutowireTrait;

  /**
   * DrushDevutilsCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\file\FileUsage\DatabaseFileUsageBackend $fileUsage
   *   Service database file usage backend.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected DatabaseFileUsageBackend $fileUsage
  ) {
    parent::__construct();
  }

  /**
   * Drush command that export uuid for entities.
   *
   * @param string $entityType
   *   Entity type to search, available [node, menu_link, block,media,
   *   file,term ].
   * @param string $filter
   *   Filter to search, all.
   * @param array $options
   *   List of options.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @command devutils:uuid
   * @aliases devutils uuid
   * @usage devutils:uuid node page
   * @option label Display label to entity
   */
  public function devutils(
    string $entityType = 'node',
    string $filter = 'all',
    array $options = ['label' => FALSE]
  ) {
    $entities = [];

    switch ($entityType) {
      case 'menu_link':
        $menu_content_storage = $this->entityTypeManager
          ->getStorage('menu_link_content');
        if ($filter == 'all') {
          $entities = $menu_content_storage->loadByProperties();
          break;
        }
        $entities = $menu_content_storage->loadByProperties(
          ['menu_name' => $filter]
        );
        break;

      case 'block':
        if ($filter === 'all') {
          $entities = $this->entityTypeManager
            ->getStorage('block_content')
            ->loadByProperties();
          break;
        }
        $entities = $this->entityTypeManager
          ->getStorage('block_content')
          ->loadByProperties(['type' => $filter]);
        break;

      case 'node':
        if ($filter === 'all') {
          $entities = $this->entityTypeManager
            ->getStorage('node')
            ->loadByProperties();
          break;
        }
        $entities = $this->entityTypeManager
          ->getStorage('node')
          ->loadByProperties(['type' => $filter]);
        break;

      case 'media':
        if ($filter === 'all') {
          $entities = $this->entityTypeManager
            ->getStorage('media')
            ->loadMultiple();
          break;
        }
        $entities = $this->entityTypeManager
          ->getStorage('media')
          ->loadByProperties(['bundle' => $filter]);
        break;

      case 'file':
        /** @var \Drupal\file\FileInterface[] $entities */
        $entities = $this->entityTypeManager
          ->getStorage('file')
          ->loadMultiple();
        break;

      case 'term':
        if ($filter === 'all') {
          $entities = $this->entityTypeManager
            ->getStorage('taxonomy_term')
            ->loadMultiple();
          break;
        }
        $entities = $this->entityTypeManager
          ->getStorage('taxonomy_term')
          ->loadByProperties(['vid' => $filter]);
        break;

      case 'paragraph':
        if ($filter === 'all') {
          $entities = $this->entityTypeManager
            ->getStorage('paragraph')
            ->loadMultiple();
          break;
        }
        $entities = $this->entityTypeManager
          ->getStorage('paragraph')
          ->loadByProperties(['type' => $filter]);
        break;
    }

    foreach ($entities as $entity) {
      if ($options['label']) {
        $this->output()->writeln('# ' . $entity->label());
      }
      $this->output()->writeln('- ' . $entity->uuid());
    }
  }

  /**
   * Drush command that clear not used file.
   *
   * @usage devutils:clear-files
   *
   * @command devutils:clear-files
   * @aliases devutils clear-files
   */
  public function clearFiles() {
    /** @var \Drupal\file\FileInterface[] $files */
    $files = [];
    try {
      $files = $this->entityTypeManager
        ->getStorage('file')
        ->loadByProperties();
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      $this->output()
        ->writeln("Error uploading files: {$e->getMessage()}.");
    }

    foreach ($files as $file) {
      $listUsage = $this->fileUsage->listUsage($file);
      if (count($listUsage) == 0) {
        try {
          $file->delete();
          $this->output()->writeln('Delete file:' . $file->label());
        }
        catch (EntityStorageException $e) {
          $this->output()
            ->writeln("Error when deleting file: {$file->label()}, {$e->getMessage()}.");
        }
      }
    }
  }

}
