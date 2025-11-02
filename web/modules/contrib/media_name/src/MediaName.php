<?php

namespace Drupal\media_name;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\media\MediaInterface;

/**
 * Class MediaName.
 */
class MediaName implements MediaNameInterface {

  /**
   * Drupal\Core\Entity\EntityFieldManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new MediaName object.
   */
  public function __construct(
    EntityFieldManagerInterface $entity_field_manager,
    ConfigFactory $config_factory,
    MessengerInterface $messenger,
    LoggerChannelFactoryInterface $logger_factory
  ) {
    $this->entityFieldManager = $entity_field_manager;
    $this->configFactory = $config_factory;
    $this->messenger = $messenger;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function alterMediaEditForm(FormStateInterface $form_state) {
    // Get the media bundle file field.
    $media = $this->getFormMedia($form_state);
    // On create, skip.
    // The default behaviour works as expected for this module use case:
    // if the name is empty, use the file name, otherwise preserve
    // the name override.
    if ($media->isNew()) {
      return;
    }

    $file = $this->getMediaFile($media);
    if (!is_null($file)) {
      $form_state->addBuildInfo(self::MEDIA_NAME_ORIGINAL_VALUE, $media->label());
      $form_state->addBuildInfo(self::FILE_NAME_ORIGINAL_VALUE, $file->getFilename());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mediaEditFormSubmitHandler(FormStateInterface $form_state) {
    $buildInfo = $form_state->getBuildInfo();
    // Skip if there might be other form state alterations.
    if (empty($buildInfo[self::MEDIA_NAME_ORIGINAL_VALUE])
        || empty($buildInfo[self::FILE_NAME_ORIGINAL_VALUE])
        || empty($form_state->getValue('name')[0]['value'])
    ) {
      return;
    }

    $media = $this->getFormMedia($form_state);
    $file = $this->getMediaFile($media);

    $fileName = $file->getFilename();
    $fileNameOriginal = $buildInfo[self::FILE_NAME_ORIGINAL_VALUE];
    $mediaName = $form_state->getValue('name')[0]['value'];
    $mediaNameOriginal = $buildInfo[self::MEDIA_NAME_ORIGINAL_VALUE];

    // Always skip if the file name has not changed.
    // Checking the name instead of the file id as other modules like
    // media_entity_file_replace could be enabled.
    if ($fileName === $fileNameOriginal) {
      return;
    }

    // Optionally skip if the file name was the same as the Media name.
    // This is the core default behaviour.
    // It is presumed that the file name needs to be updated then,
    // so this is the default module configuration.
    // Example:
    // - Original media name = File name = file1.jpg
    // - Replace File by file2.jpg -> Media name becomes file2.jpg.
    $skip = $this->configFactory->get('media_name.settings')->get('file_name_override');
    if (
      // Skipping only applies if the Media name
      // has not been set manually in the same operation.
      $mediaNameOriginal === $mediaName &&
      $fileNameOriginal === $mediaNameOriginal &&
      $skip === 1
    ) {
      return;
    }

    // If the file name has changed, it will override the Media name.
    // In this case only, setting the original Media name back.
    $media->setName($mediaName);
    try {
      $media->save();
    }
    catch (\Exception $exception) {
      $this->loggerFactory->get('media_name')->error($exception->getMessage());
      $this->messenger->addError($exception->getMessage());
    }
  }

  /**
   * Returns the Media entity from a Form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return \Drupal\media\MediaInterface
   *   Media entity.
   */
  private function getFormMedia(FormStateInterface $form_state) {
    $formObject = $form_state->getFormObject();
    return $formObject->getEntity();
  }

  /**
   * Returns the File from the File field of a Media entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   File name if it is found or NULL.
   */
  private function getMediaFile(MediaInterface $media) {
    $result = NULL;
    $fields = $this->entityFieldManager->getFieldDefinitions('media', $media->bundle());
    foreach ($fields as $key => $field) {
      // Only get file related fields, exclude other fields
      // and then Media bundles that are not containing files
      // (e.g. Remote video).
      if ($field instanceof FieldConfigInterface
        && in_array($field->getType(), ['file', 'image'])
        && $media->hasField($field->getName())
        && !$media->get($field->getName())->isEmpty()
      ) {
        /*** @var \Drupal\file\FileInterface $file */
        $result = $media->get($field->getName())->referencedEntities()[0];
      }
    }
    return $result;
  }

}
