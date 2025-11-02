<?php

declare(strict_types=1);

namespace Drupal\shy\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\ckeditor\CKEditorPluginButtonsInterface;
use Drupal\ckeditor\CKEditorPluginCssInterface;
use Drupal\ckeditor\CKEditorPluginInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "SHY" plugin.
 *
 * Plugin to insert a soft hyphen (&shy;) into the content
 * by pressing Ctrl+Hyphen or using the provided button.
 *
 * @CKEditorPlugin(
 *   id = "shy",
 *   label = @Translation("Soft hyphen"),
 *   module = "shy"
 * )
 */
class Shy extends CKEditorPluginBase implements ContainerFactoryPluginInterface, CKEditorPluginInterface, CKEditorPluginButtonsInterface, CKEditorPluginCssInterface {

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * The File URL Generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */

  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Constructs a new DrupalMedia plugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extensionListModule
   *   The module extension list.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file Url generator.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleExtensionList $extensionListModule, FileUrlGeneratorInterface $fileUrlGenerator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleExtensionList = $extensionListModule;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'ShyImageIcon' => $this->fileUrlGenerator->generateString($this->moduleExtensionList->getPath('shy') . '/icons/shy.png'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->moduleExtensionList->getPath('shy') . '/js/shy.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'DrupalShy' => [
        'label' => $this->t('Soft hyphen'),
        'image' => $this->moduleExtensionList->getPath('shy') . '/icons/shy.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCssFiles(Editor $editor) {
    return [$this->moduleExtensionList->getPath('shy') . '/css/ckeditor.shy.css'];
  }

}
