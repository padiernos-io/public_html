<?php

namespace Drupal\responsive_gallery\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\image\ImageStyleStorageInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Plugin implementation of the responsive_gallery.
 *
 * @FieldFormatter(
 *  id = "responsive_image",
 *  label = @Translation("Responsive Gallery"),
 *  field_types = {"image"}
 * )
 */
class ResponsiveGalleryFormatter extends ImageFormatter {

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a MediaThumbnailFormatter object.
   *
   * @param string $plugin_id
   *   The plugin ID for the formatter.
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
   *   Any third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\image\ImageStyleStorageInterface $image_style_storage
   *   The image style entity storage handler.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, ImageStyleStorageInterface $image_style_storage, FileUrlGeneratorInterface $file_url_generator, RendererInterface $renderer, RouteMatchInterface $route_match) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings, $current_user, $image_style_storage, $file_url_generator, $route_match);
    $this->renderer = $renderer;
    $this->fileUrlGenerator = $file_url_generator;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
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
      $container->get('file_url_generator'),
      $container->get('renderer'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'image_style' => '',
      'wrapper_class' => '',
      'extra_large_devices' => '5',
      'large_devices' => '4',
      'medium_devices' => '3',
      'small_devices' => '1',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element = [];

    $image_styles = image_style_options(FALSE);
    $description_link = Link::fromTextAndUrl(
      $this->t('Configure Image Styles'),
      Url::fromRoute('entity.image_style.collection')
    );

    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#options' => $image_styles,
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#description' => $description_link->toRenderable(),
    ];

    $element['wrapper_class'] = [
      '#title' => $this->t('Wrapper class'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('wrapper_class'),
      '#description' => $this->t('Custom wrapper class'),
    ];

    $element['extra_large_devices'] = [
      '#type' => 'select',
      '#title' => $this->t('Extra large devices'),
      '#description' => $this->t('Images per row for Extra large devices.'),
      '#options' => [
        '5' => $this->t('5'),
        '4' => $this->t('4'),
        '3' => $this->t('3'),
        '2' => $this->t('2'),
      ],
      '#default_value' => $this->getSetting('extra_large_devices'),
    ];

    $element['large_devices'] = [
      '#type' => 'select',
      '#title' => $this->t('Large devices'),
      '#description' => $this->t('Images per row for large devices'),
      '#options' => [
        '5' => $this->t('5'),
        '4' => $this->t('4'),
        '3' => $this->t('3'),
        '2' => $this->t('2'),
      ],
      '#default_value' => $this->getSetting('large_devices'),
    ];

    $element['medium_devices'] = [
      '#type' => 'select',
      '#title' => $this->t('Medium devices'),
      '#description' => $this->t('Images per row for medium devices'),
      '#options' => [
        '3' => $this->t('3'),
        '2' => $this->t('2'),
        '1' => $this->t('1'),
      ],
      '#default_value' => $this->getSetting('medium_devices'),
    ];

    $element['small_devices'] = [
      '#type' => 'select',
      '#title' => $this->t('Small devices'),
      '#description' => $this->t('Images per row for small devices'),
      '#options' => [
        '2' => $this->t('2'),
        '1' => $this->t('1'),
      ],
      '#default_value' => $this->getSetting('small_devices'),
    ];

    return $element;

  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary = [];

    $image_styles = image_style_options(FALSE);

    unset($image_styles['']);

    $select_style = $this->getSetting('image_style');

    if (isset($image_styles[$select_style])) {
      $summary[] = $this->t('Image style: @style', ['@style' => $image_styles[$select_style]]);
    }
    else {
      $summary[] = $this->t('Original image');
    }

    $wrapper_class = $this->getSetting('wrapper_class');
    $extra_large_devices = $this->getSetting('extra_large_devices');
    $large_devices = $this->getSetting('large_devices');
    $medium_devices = $this->getSetting('medium_devices');
    $small_devices = $this->getSetting('small_devices');

    if ($wrapper_class != '') {
      $summary[] = $this->t('Gallery wrapper class: @wrapper_class', ['@wrapper_class' => $wrapper_class]);
    }
    $summary[] = $this->t('Images per row for extra large devices: @extra_large_devices', ['@extra_large_devices' => $extra_large_devices]);
    $summary[] = $this->t('Images per row for large devices: @large_devices', ['@large_devices' => $large_devices]);
    $summary[] = $this->t('Images per row for medium devices: @medium_devices', ['@medium_devices' => $medium_devices]);
    $summary[] = $this->t('Images per row for small devices: @small_devices', ['@small_devices' => $small_devices]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $wrapper_class = $this->getSetting('wrapper_class');
    $extra_large_devices = $this->getSetting('extra_large_devices');
    $large_devices = $this->getSetting('large_devices');
    $medium_devices = $this->getSetting('medium_devices');
    $small_devices = $this->getSetting('small_devices');

    $large_devices_class = 'rg-grid-item-elg-' . $extra_large_devices;
    $large_devices = 'rg-grid-item-lg-' . $large_devices;
    $medium_devices = 'rg-grid-item-md-' . $medium_devices;
    $small_devices = 'rg-grid-item-sm-' . $small_devices;

    $thumbnail_classes = $large_devices_class . ' ' . $large_devices . ' ' . $medium_devices . ' ' . $small_devices;

    // Build the render array to pass to the template file.
    $elements = [
      '#attached' => [
        'library' => [
        // Attach the basic library for this module to provide needed css.
          'responsive_gallery/responsive_gallery',
        ],
      ],
      '#theme' => 'responsive_gallery',
      '#main' => [],
      '#gallery' => [
        'thumbnail_classes' => $thumbnail_classes,
        'wrapper_class' => $wrapper_class ,
      ],
    ];

    $other_data = [];

    $group = '';

    $view_id = $this->routeMatch->getParameter('view_id');
    if ($view_id) {
      $route = $this->routeMatch->getRouteObject();
      $display_id = $route->getDefault('display_id');

      $group = 'gallery-' . $display_id;
    }

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $items */
    if (empty($images = $this->getEntitiesToView($items, $langcode))) {
      // Early opt-out if the field is empty.
      return $elements;
    }

    $entity = $items->getEntity();

    if ($group == '') {
      $bundle = $entity->bundle();
      $group = 'gallery-' . $bundle;
    }

    $other_data['group'] = $group;
    $other_data['wrapper_class'] = $wrapper_class;
    $other_data['wrapper_class'] = $wrapper_class;

    $image_style_setting = $this->getSetting('image_style');

    /** @var \Drupal\image\ImageStyleInterface $image_style */
    $image_style = $this->imageStyleStorage->load($this->getSetting('image_style'));
    /** @var \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator */
    $file_url_generator = $this->fileUrlGenerator;
    /** @var \Drupal\file\FileInterface[] $images */
    foreach ($images as $image) {
      $image_uri = $image->getFileUri();

      $image_uri = $file_url_generator->generateString($image_uri);

      // Add cacheability metadata from the image and image style.
      $cacheability = CacheableMetadata::createFromObject($image);
      if ($image_style) {
        $cacheability->addCacheableDependency(CacheableMetadata::createFromObject($image_style));
      }

      // Extract field item attributes for the theme function, and unset them
      // from the $item so that the field template does not re-render them.
      $item = $image->_referringItem;
      $item_attributes = $item->_attributes;

      unset($item->_attributes);

      // Collect cache tags to be added for each thumbnail in the field.
      $thumbnail_cache_tags = [];
      $thumbnail_image_style = $this->getSetting('thumbnail_image_style');
      if (!empty($thumbnail_image_style)) {
        $image_style = $this->imageStyleStorage->load($thumbnail_image_style);
        $thumbnail_cache_tags = $image_style->getCacheTags();
      }

      // Generate the thumbnail.
      // if ($thumbnailsPerRow > 0) {.
      $elements['#thumbnails']['images'][] = [
        '#theme' => 'image_formatter',
        '#item' => $item,
        '#item_attributes' => $item_attributes,
        '#prefix' => '<div data-fancybox="gallery" data-src="' . $image_uri . '">',
        '#suffix' => '</div>',
        '#image_style' => $image_style_setting,
        '#image_uri' => $image_uri,
        '#cache' => [
          'tags' => Cache::mergeTags($thumbnail_cache_tags, $image->getCacheTags()),
        ],
      ];
      // }
      // $cacheability->applyTo($elements[$delta]);
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function isBackgroundImageDisplay() {
    return $this->getPluginId() == 'responsive_gallery';
  }

}
