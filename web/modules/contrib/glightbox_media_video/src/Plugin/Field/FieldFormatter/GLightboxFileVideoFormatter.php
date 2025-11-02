<?php

namespace Drupal\glightbox_media_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileMediaFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'file_video' formatter.
 *
 * @FieldFormatter(
 *   id = "glightbox_file_video",
 *   label = @Translation("GLightbox Video Popup"),
 *   description = @Translation("Display thumbnail and opens video in GLightbox popup."),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class GLightboxFileVideoFormatter extends FileMediaFormatterBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyleStorage;

  /**
   * The field formatter plugin instance for videos.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $videoFormatter;

  /**
   * Allow us to attach glightbox settings to our element.
   *
   * @var \Drupal\glightbox\ElementAttachmentInterface
   */
  protected $glightboxAttachment;

  /**
   * Constructs a new instance of the plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   * @param \Drupal\Core\Field\FormatterInterface $video_formatter
   *   The field formatter for videos.
   * @param \Drupal\glightbox\ElementAttachmentInterface|null $glightbox_attachment
   *   The glightbox attachment if glightbox is enabled.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user,  EntityStorageInterface $image_style_storage, FormatterInterface $video_formatter, $glightbox_attachment) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->glightboxAttachment = $glightbox_attachment;
    $this->currentUser = $current_user;
    $this->imageStyleStorage = $image_style_storage;
    $this->videoFormatter = $video_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $formatter_manager = $container->get('plugin.manager.field.formatter');
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $formatter_manager->createInstance('oembed', $configuration),
      $container->get('glightbox.attachment')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getMediaType() {
    return 'video';
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'muted' => FALSE,
      'width' => 640,
      'height' => 480,
      'display' => 'thumbnail',
      'link_text' => 'View Video',
      'image_style' => 'thumbnail',
      'glightbox_gallery' => 'post',
      'glightbox_gallery_custom' => '',
      'glightbox_caption' => 'auto',
      'glightbox_caption_custom' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state) + [
      'muted' => [
        '#title' => $this->t('Muted'),
        '#type' => 'checkbox',
        '#default_value' => $this->getSetting('muted'),
      ],
      'width' => [
        '#type' => 'number',
        '#title' => $this->t('Width'),
        '#default_value' => $this->getSetting('width'),
        '#size' => 5,
        '#maxlength' => 5,
        '#field_suffix' => $this->t('pixels'),
        '#min' => 0,
        '#required' => TRUE,
      ],
      'height' => [
        '#type' => 'number',
        '#title' => $this->t('Height'),
        '#default_value' => $this->getSetting('height'),
        '#size' => 5,
        '#maxlength' => 5,
        '#field_suffix' => $this->t('pixels'),
        '#min' => 0,
        '#required' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Text that launches a modal window.');
    $summary[] = $this->t('Muted: %muted', ['%muted' => $this->getSetting('muted') ? $this->t('yes') : $this->t('no')]);
    $summary[] = $this->t('Size: %width x %height pixels', [
      '%width' => $this->getSetting('width'),
      '%height' => $this->getSetting('height'),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareAttributes(array $additional_attributes = []) {
    return parent::prepareAttributes(['muted'])
      ->setAttribute('width', $this->getSetting('width'))
      ->setAttribute('height', $this->getSetting('height'));
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getSettings();

    $source_files = $this->getSourceFiles($items, $langcode);
    if (empty($source_files)) {
      return $elements;
    }

    // Collect cache tags to be added for each item in the field.
    $cache_tags = [];
    $attributes = $this->prepareAttributes();
    foreach ($source_files as $delta => $files) {
      $ges = $files;
      // @todo Add HTML with glightbox.
      // @todo Add new library or use glightbox_media_video existing library.
      $elements[$delta] = [
        '#theme' => 'glightbox_media_file_video_formatter',
        '#file_video' => $files,
        '#thumb' => $items->getEntity()->get('thumbnail')->first(),
        '#entity' => $items->getEntity(),
        '#settings' => $settings,
        '#cache' => [
          'tags' => $cache_tags,
        ],
        '#attached' => [
          'library' => [
            'glightbox_media_video/glightbox-media-video',
          ],
        ],
      ];

      $cache_tags = [];
      foreach ($files as $file) {
        $cache_tags = Cache::mergeTags($cache_tags, $file['file']->getCacheTags());
      }
      $elements[$delta]['#cache']['tags'] = $cache_tags;
    }

    // Attach the GLightbox JS and CSS.
    if ($this->glightboxAttachment->isApplicable()) {
      $this->glightboxAttachment->attach($elements);
    }

    return $elements;
  }
}
