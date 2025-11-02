<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopScenario;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting Backstop profiles.
 *
 * @package Drupal\backstop_generator\Form
 */
class BackstopProfileDeleteForm extends EntityConfirmFormBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new BackstopProfileDeleteForm object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(FileSystemInterface $file_system, ConfigFactoryInterface $config_factory) {
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %label profile?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return $this->t('Delete Profile');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the scenarios.
    $this->removeScenarios();
    // Delete the entity.
    if ($this->removeProfileFile()) {
      $this->entity->delete();
      parent::submitForm($form, $form_state);
    }
  }

  /**
   * Remove the backstop.json file from the file system.
   *
   * @return mixed
   *   Returns TRUE on success, exception on failure.
   *
   * @throws \Drupal\Core\File\Exception\NotRegularFileException
   */
  private function removeProfileFile() {
    // Delete the backstop.json file and its parent directory.
    $project_dir = dirname(DRUPAL_ROOT);
    $backstop_dir = $this->configFactory->get('backstop_generator.settings')->get('backstop_directory');

    // Get the entity's id.
    $id = $this->entity->id();
    // Delete the file.
    return $this->fileSystem->delete($project_dir . "$backstop_dir/bsg_{$id}.json");
  }

  /**
   * Remove the scenarios associated with the profile.
   *
   * @return void
   *   No return value.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function removeScenarios() {
    $scenarios = $this->configFactory->listAll("backstop_generator.scenario.{$this->entity->id()}_");
    foreach ($scenarios as $scenario) {
      $sid = substr($scenario, 28);
      BackstopScenario::load($sid)->delete();
    }
  }

}
