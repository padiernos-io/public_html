<?php

namespace Drupal\glightbox_media_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\media\Plugin\Field\FieldFormatter\OEmbedFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'glightbox' formatter for Media Remote Video
 *
 * @FieldFormatter(
 *   id = "glightbox_media_remote_video",
 *   module = "glightbox_media_video",
 *   label = @Translation("GLightbox Media Remote Video"),
 *   field_types = {
 *     "link",
 *     "string",
 *     "string_long",
 *   },
 * )
 */
class GLightboxMediaRemoteVideoFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

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
   * Drupal\Core\Extension\ModuleHandlerInterface definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Field\FormatterInterface $video_formatter
   *   The field formatter for videos.
   * @param \Drupal\glightbox\ElementAttachmentInterface|null $glightbox_attachment
   *   The glightbox attachment if glightbox is enabled.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler services.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityStorageInterface $image_style_storage, RendererInterface $renderer, FormatterInterface $video_formatter, $glightbox_attachment,  ModuleHandlerInterface $moduleHandler) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->currentUser = $current_user;
    $this->imageStyleStorage = $image_style_storage;
    $this->videoFormatter = $video_formatter;
    $this->renderer = $renderer;
    $this->glightboxAttachment = $glightbox_attachment;
    $this->moduleHandler = $moduleHandler;
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
      $container->get('renderer'),
      $formatter_manager->createInstance('oembed', $configuration),
      $container->get('glightbox.attachment'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return OEmbedFormatter::defaultSettings() + [
      'display' => 'thumbnail',
      'link_text' => 'View Video',
      'image_style' => 'thumbnail',
      'glightbox_gallery' => 'post',
      'glightbox_gallery_custom' => '',
      'glightbox_caption' => 'auto',
      'glightbox_caption_custom' => '',
      'glightbox_caption_description' => '',
      'glightbox_caption_description_custom' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $element = parent::settingsForm($form, $form_state);
    $element += $this->videoFormatter->settingsForm([], $form_state);
    $element['display'] = [
      '#type' => 'select',
      '#title' => $this->t('Display'),
      '#default_value' => $this->getSetting('display'),
      '#options' => [
        'thumbnail' => $this->t('Thumbnail'),
        'text' => $this->t('Text'),
        'media_title' => $this->t('Media title'),
      ],
    ];
    $element['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->getSetting('link_text'),
      '#states' => [
        'visible' => [
          [':input[name="options[settings][display]"]' => ['value' => 'text']],
        ],
      ],
    ];

    $description_link = Link::fromTextAndUrl(
      $this->t('Configure Image Styles'),
      Url::fromRoute('entity.image_style.collection')
    );

    $element['image_style'] = [
      '#title' => $this->t('Thumbnail image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles,
      '#description' => $description_link->toRenderable() + [
          '#access' => $this->currentUser->hasPermission('administer image styles'),
        ],
      '#states' => [
        'visible' => [
          [':input[name="options[settings][display]"]' => ['value' => 'thumbnail']],
        ],
      ],
    ];

    $gallery = [
      'post' => $this->t('Per post gallery'),
      'page' => $this->t('Per page gallery'),
      'parent' => $this->t('Per parent entity gallery (e.g. Media field or Paragraph with video fields)'),
      'paragraph' => $this->t('Per paragraph gallery (e.g. Paragraphs with Media fields)'),
      'field_post' => $this->t('Per field in post gallery'),
      'field_page' => $this->t('Per field in page gallery'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('No gallery'),
    ];
    $element['glightbox_gallery'] = [
      '#title' => $this->t('Gallery (video grouping)'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('glightbox_gallery'),
      '#options' => $gallery,
      '#description' => $this->t('How GLightbox should group the video galleries.'),
    ];
    $element['glightbox_gallery_custom'] = [
      '#title' => $this->t('Custom gallery'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('glightbox_gallery_custom'),
      '#description' => $this->t('All images on a page with the same gallery value (rel attribute) will be grouped together. It must only contain lowercase letters, numbers, and underscores.'),
      '#required' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][glightbox_gallery]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    if ($this->moduleHandler->moduleExists('token')) {

      $entity_type = '';

      if (isset($form['#entity_type']) && !empty($form['#entity_type'])) {
        $entity_type = $form['#entity_type'];
      }

      $element['glightbox_token_gallery'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Replacement patterns'),
        '#theme' => 'token_tree_link',
        '#token_types' => [$entity_type, 'file'],
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][glightbox_gallery]"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }
    else {
      $element['glightbox_token_gallery'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Replacement patterns'),
        '#description' => '<strong class="error">' . $this->t('For token support the <a href="@token_url">token module</a> must be installed.', ['@token_url' => 'http://drupal.org/project/token']) . '</strong>',
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][glightbox_gallery]"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }

    $caption = [
      'auto' => $this->t('Automatic'),
      'title' => $this->t('Title text'),
      'alt' => $this->t('Alt text'),
      'entity_title' => $this->t('Content title'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('None'),
    ];
    $element['glightbox_caption'] = [
      '#title' => $this->t('Caption'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('glightbox_caption'),
      '#options' => $caption,
      '#description' => $this->t('Automatic will use the first non-empty value out of the title, the alt text and the content title.'),
    ];
    $element['glightbox_caption_custom'] = [
      '#title' => $this->t('Custom caption'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('glightbox_caption_custom'),
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][glightbox_caption]"]' => ['value' => 'custom'],
        ],
      ],
    ];
    $element['glightbox_caption_description'] = [
      '#title' => $this->t('Caption description'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('glightbox_caption_description'),
      '#options' => $caption,
      '#description' => $this->t('Same as caption but supports HTML.'),
    ];
    $element['glightbox_caption_description_custom'] = [
      '#title' => $this->t('Custom caption description'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('glightbox_caption_description_custom'),
      '#states' => [
        'visible' => [
          ':input[name$="[settings_edit_form][settings][glightbox_caption_description]"]' => ['value' => 'custom'],
        ],
      ],
    ];

    if ($this->moduleHandler->moduleExists('token')) {

      $entity_type = '';

      if (isset($form['#entity_type']) && !empty($form['#entity_type'])) {
        $entity_type = $form['#entity_type'];
      }

      $element['glightbox_token_caption'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Replacement patterns'),
        '#theme' => 'token_tree_link',
        '#token_types' => [$entity_type, 'file', 'paragraph'],
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][glightbox_caption]"]' => ['value' => 'custom'],
            'or',
            ':input[name$="[settings_edit_form][settings][glightbox_caption_description]"]' => ['value' => 'custom'],
          ],
        ],
      ];
    }
    else {
      $element['glightbox_token_caption'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Replacement patterns'),
        '#description' => '<strong class="error">' . $this->t('For token support the <a href="@token_url">token module</a> must be installed.', ['@token_url' => 'http://drupal.org/project/token']) . '</strong>',
        '#states' => [
          'visible' => [
            ':input[name$="[settings_edit_form][settings][glightbox_caption]"]' => ['value' => 'custom'],
            'or',
            ':input[name$="[settings_edit_form][settings][glightbox_caption_description]"]' => ['value' => 'custom'],

          ],
        ],
      ];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Text that launches a modal window.');
    if ($video_settings_summary = $this->videoFormatter->settingsSummary()) {
      $summary[] = reset($video_settings_summary);
    }
    if ($this->getSetting('display') == 'text') {
      $summary[] = $this->t('Link text: @link_text.', [
        '@link_text' => $this->getSetting('link_text'),
      ]);
    }
    else {
      $image_styles = image_style_options(FALSE);
      if (isset($image_styles[$this->getSetting('image_style')])) {
        $summary[] = $this->t('Thumbnail image style: @image_style.', [
          '@image_style' => $image_styles[$this->getSetting('image_style')],
        ]);
      }
      else {
        $summary[] = $this->t('GLightbox image style: Original image');
      }
    }

    $gallery = [
      'post' => $this->t('Per post gallery'),
      'page' => $this->t('Per page gallery'),
      'parent' => $this->t('Per parent entity gallery (e.g. Media field or Paragraph with video fields)'),
      'paragraph' => $this->t('Per paragraph gallery (e.g. Paragraphs with Media fields)'),
      'field_post' => $this->t('Per field in post gallery'),
      'field_page' => $this->t('Per field in page gallery'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('No gallery'),
    ];

    if ($this->getSetting('glightbox_gallery')) {
      $summary[] = $this->t('GLightbox gallery type: @type', ['@type' => $gallery[$this->getSetting('glightbox_gallery')]]) . ($this->getSetting('glightbox_gallery') == 'custom' ? ' (' . $this->getSetting('glightbox_gallery_custom') . ')' : '');
    }

    $caption = [
      'auto' => $this->t('Automatic'),
      'title' => $this->t('Title text'),
      'alt' => $this->t('Alt text'),
      'entity_title' => $this->t('Content title'),
      'custom' => $this->t('Custom (with tokens)'),
      'none' => $this->t('None'),
    ];

    if ($this->getSetting('glightbox_caption')) {
      $summary[] = $this->t('GLightbox caption: @type', ['@type' => $caption[$this->getSetting('glightbox_caption')]]);
    }

    if ($this->getSetting('glightbox_caption_description')) {
      $summary[] = $this->t('GLightbox caption description: @type', ['@type' => $caption[$this->getSetting('glightbox_caption')]]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = [];
    $settings = $this->getSettings();
    $videos = $this->videoFormatter->viewElements($items, $langcode);

    // Early opt-out if the field is empty.
    if (empty($videos)) {
      return $elements;
    }

    // Collect cache tags to be added for each item in the field.
    $cache_tags = [];
    if ($settings['display'] == 'thumbnail' && !empty($settings['image_style'])) {
      $image_style = $this->imageStyleStorage->load($settings['image_style']);
      $cache_tags = $image_style->getCacheTags();
    }

    foreach ($videos as $delta => $video) {
      $cache_tags = Cache::mergeTags($cache_tags, $video['#cache']['tags']);
      $elements[$delta] = [
        '#theme' => 'glightbox_media_remote_video_formatter',
        '#remote_video' => $video,
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
    }

    // Attach the GLightbox JS and CSS.
    if ($this->glightboxAttachment->isApplicable()) {
      $this->glightboxAttachment->attach($elements);
    }

    return $elements;
  }
}
