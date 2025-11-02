<?php

namespace Drupal\ckeditor5_markdown_editor\Command\Drush;

use Drush\Commands\DrushCommands;

/**
 * ckeditor5 markdown output Drush command file.
 */
class CKEditorMarkdownEditorCommands extends DrushCommands {

  /**
   * The CKEditor plugin installation command.
   *
   * @var \Drupal\ckeditor5_markdown_editor\Command\Drush\InstallCommand
   */
  protected InstallCommand $installCommand;

  /**
   * The CKEditor plugin update command.
   *
   * @var \Drupal\ckeditor5_markdown_editor\Command\Drush\UpdateCommand
   */
  protected UpdateCommand $updateCommand;

  /**
   * Constructs ckeditor5 markdown output Drush Command object.
   *
   * @param \Drupal\ckeditor5_markdown_editor\Command\Drush\InstallCommand $installCommand
   *   The CKEditor plugin installation command.
   * @param \Drupal\ckeditor5_markdown_editor\Command\Drush\UpdateCommand $updateCommand
   *   The CKEditor plugin update command.
   */
  public function __construct(InstallCommand $installCommand, UpdateCommand $updateCommand) {
    parent::__construct();

    $this->installCommand = $installCommand;
    $this->updateCommand = $updateCommand;
  }

  /**
   * Install library dependencies for the ckeditor5 markdown output plugin.
   *
   * @command ckeditor5_markdown_editor:install
   */
  public function install(): void {
    $this->installCommand->execute($this->input(), $this->output(), $this->io());
  }

  /**
   * Update library dependencies for the ckeditor5 markdown output plugin.
   *
   * @command ckeditor5_markdown_editor:update
   */
  public function update(): void {
    $this->updateCommand->execute($this->input(), $this->output(), $this->io());
  }

}
