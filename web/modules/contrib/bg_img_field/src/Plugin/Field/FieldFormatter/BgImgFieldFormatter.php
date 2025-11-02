<?php

namespace Drupal\bg_img_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\bg_img_field\Component\Render\CSSSnippet;
use Drupal\breakpoint\BreakpointManagerInterface;
use Drupal\responsive_image\Plugin\Field\FieldFormatter\ResponsiveImageFormatter;
use Drupal\token\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin implementation of the 'image' formatter.
 *
 * @FieldFormatter(
 *   id = "bg_img_field_formatter",
 *   label = @Translation("Background Image Field Widget"),
 *   field_types = {
 *     "bg_img_field"
 *   },
 *   quickedit = {
 *     "editor" = "image"
 *   }
 * )
 */
class BgImgFieldFormatter extends ResponsiveImageFormatter implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * The token service.
   *
   * @var \Drupal\token\TokenServiceInterface
   */
  protected $tokenService;

  /**
   * The RequestStack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Breakpoint Manager service.
   *
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The file URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Constructor for the Background Image Formatter.
   *
   * @param string $plugin_id
   *   The plugin unique id.
   * @param string $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $settings
   *   The stored setting for the formatter.
   * @param string $label
   *   The formatter's label.
   * @param string $view_mode
   *   Which view mode the formatter is in.
   * @param array $third_party_settings
   *   Any third party setting that might change how the formatter renders the
   *   CSS.
   * @param \Drupal\Core\Entity\EntityStorageInterface $responsive_image_style_storage
   *   The responsive image styles created in the system.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image styles that have been created in the system.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   Help generate links.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\breakpoint\BreakpointManagerInterface $breakpoint_manager
   *   The breakpoint manager service.
   * @param \Drupal\token\TokenServiceInterface $token_service
   *   The token service used to generate tokens.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack for the current request context.
   * @param \Drupal\Core\Url\UrlGeneratorInterface $url_generator
   *   The URL generator for generating URLs for routes.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator to generate absolute file URLs.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    EntityStorageInterface $responsive_image_style_storage,
    EntityStorageInterface $image_style_storage,
    LinkGeneratorInterface $link_generator,
    AccountInterface $current_user,
    BreakpointManagerInterface $breakpoint_manager,
    Token $token_service,
    RequestStack $request_stack,
    UrlGeneratorInterface $url_generator,
    FileUrlGeneratorInterface $file_url_generator,
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings,
      $responsive_image_style_storage,
      $image_style_storage,
      $link_generator,
      $current_user
    );

    $this->breakpointManager = $breakpoint_manager;
    $this->tokenService = $token_service;
    $this->requestStack = $request_stack;
    $this->urlGenerator = $url_generator;
    $this->fileUrlGenerator = $file_url_generator;
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
      $container->get('entity_type.manager')->getStorage('responsive_image_style'),
      $container->get('entity_type.manager')->getStorage('image_style'),
      $container->get('link_generator'),
      $container->get('current_user'),
      $container->get('breakpoint.manager'),
      $container->get('token'),
      $container->get('request_stack'),
      $container->get('url_generator'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    // Get the options for responsive image styles.
    $options = $elements['responsive_image_style']['#options'];
    // New options array for storing new option values.
    $new_options = [];
    // Loop through the options to locate only the ones that are labeled
    // image styles. This will eliminate any by size styles.
    foreach ($options as $key => $option) {
      $storage = $this->responsiveImageStyleStorage->load($key);
      $image_style_mappings = $storage->get('image_style_mappings');
      if (isset($image_style_mappings[0]) && $image_style_mappings[0]['image_mapping_type']
      === 'image_style') {
        $new_options += [$key => $option];
      }
    }
    $elements['responsive_image_style']['#options'] = $new_options;
    // Remove the image link element.
    unset($elements['image_link']);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    if ($responsive_image_style) {
      $summary[] = $this->t('Responsive image style: @responsive_image_style',
        ['@responsive_image_style' => $responsive_image_style->label()]);
    }
    else {
      $summary[] = $this->t('Select a responsive image style.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $files = $this->getEntitiesToView($items, $langcode);
    $entity = $items->getEntity();

    // Early opt-out if the field is empty.
    if (empty($files)) {
      return $elements;
    }

    $image_link_setting = $this->getSetting('image_link');

    $cache_contexts = [];
    if ($image_link_setting == 'file') {
      $cache_contexts[] = 'url.site';
    }
    // Collect cache tags to be added for each item in the field.
    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }

    // Get image styles.
    $image_styles = $this->imageStyleStorage->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }

    // Process the files to get the css markup.
    foreach ($files as $file) {
      $item = $file->_referringItem;
      $selector = $item->css_selector;
      $selector = $this->tokenService->replace($selector, [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      $css = $this->generateBackgroundCss(
        $file,
        $responsive_image_style,
        $selector,
        $item->toArray()
      );

      // Attach to head on element to create style tag in the html head.
      if (!empty($css)) {
        $current_path = $this->requestStack->getCurrentRequest()->getRequestUri();
        if (preg_match('/node\/(\d+)\/layout/', $current_path, $matches)) {
          $elements = [
            '#theme' => 'background_style',
            '#css' => $css,
            '#cache' => [
              'tags' => $cache_tags,
              'contexts' => $cache_contexts,
            ],
          ];
        }
        else {
          // Use the selector in the id to avoid collisions with multiple
          // background formatters on the same page.
          $id = 'picture-background-formatter-' . $selector;
          $elements['#attached']['html_head'][] = [[
            '#tag' => 'style',
            '#value' => new CSSSnippet($css),
            '#cache' => [
              'tags' => $cache_tags,
              'contexts' => $cache_contexts,
            ],
          ], $id,
          ];
        }
      }
    }

    return $elements;
  }

  /**
   * CSS Generator Helper Function.
   *
   * @param Drupal\Core\Entity\EntityInterface $file
   *   URI of the field image.
   * @param string $responsive_image_style
   *   Desired picture mapping to generate CSS.
   * @param string $selector
   *   CSS selector to target.
   * @param array $options
   *   CSS options.
   *
   * @return string
   *   Generated background image CSS.
   */
  protected function generateBackgroundCss(EntityInterface $file, $responsive_image_style, $selector, array $options) {
    $css = "";

    if (!$file) {
      return $css;
    }

    $css .= $selector . '{';
    $css .= "background-repeat: " . $options['css_repeat'] . ";";
    $css .= "background-size: " . $options['css_background_size'] . ";";
    $css .= "background-position: " . $options['css_background_position'] . ";";
    $css .= '}';

    // $responsive_image_style holds the configuration from the responsive_image
    // module for a given responsive style
    // We need to check that this exists or else we get a WSOD.
    if (!$responsive_image_style) {
      $field_definition = $this->fieldDefinition->getFieldStorageDefinition();

      $this->getLogger('bg_img_field')->error('
        There is no responsive image style set for the {field_name} field on the {entity_type} entity. Please ensure
        that the responsive image style is configured at <a href="{link}">{link}</a>.  Then set the correct style on the
        formatter for the entity display.
      ', [
        'field_name' => $field_definition->get('field_name'),
        'entity_type' => $field_definition->get('entity_type'),
        'link' => $this->urlGenerator->generateFromRoute(
          'entity.responsive_image_style.collection'
        ),
      ]);
    }
    else {
      $breakpoints = $this->breakpointManager->getBreakpointsByGroup($responsive_image_style->getBreakpointGroup());
      foreach (array_reverse($responsive_image_style->getKeyedImageStyleMappings()) as $breakpoint_id => $multipliers) {
        if (isset($breakpoints[$breakpoint_id])) {
          $multipliers = array_reverse($multipliers);
          $query = $breakpoints[$breakpoint_id]->getMediaQuery();

          if ($query != "") {
            $css .= ' @media ' . $query . ' {';
          }

          foreach ($multipliers as $multiplier => $mapping) {
            $multiplier = rtrim($multiplier, "x");

            if ($mapping['image_mapping_type'] != 'image_style') {
              continue;
            }

            if ($mapping['image_mapping'] == "_original image_") {
              $url = $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
            }
            else {
              $image_style = $this->imageStyleStorage->load($mapping['image_mapping']);
              $url = $image_style->buildUrl($file->getFileUri());
            }

            if ($multiplier != 1) {
              $css .= ' @media (-webkit-min-device-pixel-ratio: ' . $multiplier . '), (min-resolution: ' . $multiplier * 96 . 'dpi), (min-resolution: ' . $multiplier . 'dppx) {';
            }
            $css .= $selector . ' {background-image: url(' . $url . ');}';

            if ($multiplier != 1) {
              $css .= '}';
            }
          }

          if ($query != "") {
            $css .= '}';
          }
        }
      }
    }

    return $css;
  }

}
