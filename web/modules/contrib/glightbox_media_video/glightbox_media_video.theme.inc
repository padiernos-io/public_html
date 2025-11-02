<?php

/**
 * @file
 * GLightbox Media Video theme functions.
 */

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;

/**
 * Prepares variables for glightbox_media_remote_video_formatter formatter templates.
 *
 * Default template: glightbox-media-remote-video-formatter.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: An Media object.
 *   - entity: An entity object.
 *   - settings: Formatter settings array.
 *
 * @codingStandardsIgnoreStart
 */
function template_preprocess_glightbox_media_remote_video_formatter(&$variables) {
  // @codingStandardsIgnoreEnd
  static $gallery_token = NULL;

  $config = \Drupal::config('glightbox.settings');
  $item = $variables['thumb'];
  $entity = $variables['entity'];
  $settings = $variables['settings'];
  $image_uri = $item->entity->getFileUri();
  $classes_array = ['glightbox', 'glightbox-media-video'];

  // Build the caption.
  $entity_title = $entity->label();
  $entity_type = $entity->getEntityTypeId();
  $entity_bundle = $entity->bundle();

  switch ($settings['glightbox_caption']) {
    case 'auto':
      // If the title is empty use alt or the entity title in that order.
      if (!empty($item->title)) {
        $caption = $item->title;
      }
      elseif (!empty($item->alt)) {
        $caption = $item->alt;
      }
      elseif (!empty($entity_title)) {
        $caption = $entity_title;
      }
      else {
        $caption = '';
      }
      break;

    case 'title':
      $caption = $item->title;
      break;

    case 'alt':
      $caption = $item->alt;
      break;

    case 'entity_title':
      $caption = $entity_title;
      break;

    case 'custom':
      $token_service = \Drupal::token();
      $data = [$entity_type => $entity, 'file' => $item];
      if (!empty($entity->_referringItem)) {
        $parent = $entity->_referringItem->getEntity();
        if ($parent->getEntityTypeId() == 'paragraph') {
          $data['paragraph'] = $parent;
        }
      }
      $caption = $token_service->replace($settings['glightbox_caption_custom'], $data, ['clear' => TRUE]);
      break;

    default:
      $caption = '';
  }

  // Shorten the caption for the example styles or when caption
  // shortening is active.
  $config = \Drupal::config('glightbox.settings');
  $glightbox_style = $config->get('image_style');
  $trim_length = $config->get('glightbox_caption_trim_length');
  if (((strpos($glightbox_style ?? '', 'glightbox/example') !== FALSE) || $config->get('glightbox_caption_trim')) && (mb_strlen($caption ?? '') > $trim_length)) {
    $caption = mb_substr($caption, 0, $trim_length - 5) . '...';
  }

  // Build the gallery id.
  $id = $entity->id();
  $entity_id = !empty($id) ? $entity_bundle . '-' . $id : 'entity-id';
  $field_name = $item->getParent()->getName();

  $parent_entity_id = '';
  $parent_paragraph_id = '';
  if (!empty($entity->_referringItem)) {
    $parent = $entity->_referringItem->getEntity();
    if ($parent instanceof EntityInterface) {
      $parent_entity_id = $parent->id();
    }

    if ($parent->getEntityTypeId() == 'paragraph') {
      $paragraph_parent = $parent->getParentEntity();
      if ($paragraph_parent instanceof EntityInterface) {
        $parent_paragraph_id = $paragraph_parent->id();
      }
    }
  }

  switch ($settings['glightbox_gallery']) {
    case 'post':
      $gallery_id = 'gallery-' . $entity_id;
      break;

    case 'page':
      $gallery_id = 'gallery-all';
      break;

    case 'parent':
      $gallery_id = 'gallery-parent-entity-' . $parent_entity_id;
      break;

    case 'paragraph':
      $gallery_id = 'gallery-parent-paragraph-' . $parent_paragraph_id;
      break;

    case 'field_post':
      $gallery_id = 'gallery-' . $entity_id . '-' . $field_name;
      break;

    case 'field_page':
      $gallery_id = 'gallery-' . $field_name;
      break;

    case 'custom':
      $token_service = \Drupal::token();
      $gallery_id = $token_service->replace($settings['glightbox_gallery_custom'], [$entity_type => $entity, 'file' => $item], ['clear' => TRUE]);
      break;

    default:
      $gallery_id = '';
  }

  // If gallery id is not empty add unique per-request token to avoid
  // images being added manually to galleries.
  if (!empty($gallery_id) && $config->get('advanced.unique_token')) {
    // Check if gallery token has already been set, we need to reuse
    // the token for the whole request.
    if (is_null($gallery_token)) {
      // We use a short token since randomness is not critical.
      $gallery_token = Crypt::randomBytesBase64(8);
    }
    $gallery_id = $gallery_id . '-' . $gallery_token;
  }

  // Set up the $variables['image'] parameter.
  if ($settings['display'] == 'text') {
    $variables['image'] = [
      '#markup' => $settings['link_text'],
    ];
  }
  elseif($settings['display']== 'media_title') {
    $variables['image'] = [
      '#markup' => $entity_title,
    ];
  }
  elseif (!empty($settings['image_style'])) {
    $variables['image'] = [
      '#theme' => 'image_style',
      '#style_name' => $settings['image_style'],
    ];
  }
  else {
    $variables['image'] = [
      '#theme' => 'image',
    ];
  }

  if (!empty($variables['image'])) {
    $variables['image']['#attributes'] = [];

    // Do not output an empty 'title' attribute.
    if (mb_strlen($item->title ?? '') != 0) {
      $variables['image']['#title'] = $item->title;
    }

    foreach (['width', 'height', 'alt'] as $key) {
      $variables['image']["#$key"] = $item->$key;
    }

    $variables['image']['#uri'] = empty($item->uri) ? $image_uri : $item->uri;
  }

  if ($caption = _glightbox_media_video_formatter_get_caption($variables)) {
    $variables['attributes']['title'] = $caption;
    $variables['attributes']['data-glightbox'] = "title: $caption";
  }

  if ($caption_description = _glightbox_media_video_formatter_get_caption($variables, 'glightbox_caption_description', FALSE)) {
    $variables['caption_description'] = $caption_description;
    $variables['attributes']['data-glightbox'] = "description: $caption_description";
  }

  if (!empty($variables['entity']) && $variables['entity']->bundle() == 'remote_video' &&
    $variables['entity']->hasField('field_media_oembed_video') &&
    !$variables['entity']->get('field_media_oembed_video')->isEmpty()) {
    $variables['attributes']['href'] = $variables['entity']->field_media_oembed_video->value;

    $youtube_id = _glightbox_media_video_extract_youtube_video_id($variables['attributes']['href']);
    if (!empty($youtube_id)) {
      $variables['attributes']['href'] = 'https://www.youtube-nocookie.com/embed/' . $youtube_id;
    }
    // @todo Add prepare_alter() hook for another source providers. $variables['attributes']['href']
    \Drupal::moduleHandler()->alter('url', $variables['attributes']['href']);
  }

  // @todo Check if it's Local Video

  if ($gallery_id) {
    $variables['attributes']['data-gallery'] = $gallery_id;
  }

  $variables['attributes']['class'] = $classes_array;
}

/**
 * Prepares variables for glightbox_media_remote_video_formatter formatter templates.
 *
 * Default template: glightbox-media-remote-video-formatter.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: An Media object.
 *   - entity: An entity object.
 *   - settings: Formatter settings array.
 *
 * @codingStandardsIgnoreStart
 */
function template_preprocess_glightbox_media_file_video_formatter(&$variables) {
  // @codingStandardsIgnoreEnd
  static $gallery_token = NULL;

  $item = $variables['thumb'];
  $entity = $variables['entity'];
  $settings = $variables['settings'];
  if ($item->entity) {
    $image_uri = $item->entity->getFileUri();
  }
  $classes_array = ['glightbox', 'glightbox-media-video'];

  // Build the caption.
  $entity_title = $entity->label();
  $entity_type = $entity->getEntityTypeId();
  $entity_bundle = $entity->bundle();

  switch ($settings['glightbox_caption']) {
    case 'auto':
      // If the title is empty use alt or the entity title in that order.
      if (!empty($item->title)) {
        $caption = $item->title;
      }
      elseif (!empty($item->alt)) {
        $caption = $item->alt;
      }
      elseif (!empty($entity_title)) {
        $caption = $entity_title;
      }
      else {
        $caption = '';
      }
      break;

    case 'title':
      $caption = $item->title;
      break;

    case 'alt':
      $caption = $item->alt;
      break;

    case 'entity_title':
      $caption = $entity_title;
      break;

    case 'custom':
      $token_service = \Drupal::token();
      $caption = $token_service->replace($settings['glightbox_caption_custom'], [$entity_type => $entity, 'file' => $item], ['clear' => TRUE]);
      break;

    default:
      $caption = '';
  }

  // Shorten the caption for the example styles or when caption
  // shortening is active.
  $config = \Drupal::config('glightbox.settings');
  $glightbox_style = $config->get('image_style');
  $trim_length = $config->get('glightbox_caption_trim_length');
  if (((strpos($glightbox_style ?? '', 'glightbox/example') !== FALSE) || $config->get('glightbox_caption_trim')) && (mb_strlen($caption ?? '') > $trim_length)) {
    $caption = mb_substr($caption, 0, $trim_length - 5) . '...';
  }

  // Build the gallery id.
  $id = $entity->id();
  $entity_id = !empty($id) ? $entity_bundle . '-' . $id : 'entity-id';
  $field_name = $item->getParent()->getName();

  $parent_entity_id = '';
  $parent_paragraph_id = '';
  if (!empty($entity->_referringItem)) {
    $parent = $entity->_referringItem->getEntity();
    if ($parent instanceof EntityInterface) {
      $parent_entity_id = $parent->id();
    }

    if ($parent->getEntityTypeId() == 'paragraph') {
      $paragraph_parent = $parent->getParentEntity();
      if ($paragraph_parent instanceof EntityInterface) {
        $parent_paragraph_id = $paragraph_parent->id();
      }
    }
  }

  switch ($settings['glightbox_gallery']) {
    case 'post':
      $gallery_id = 'gallery-' . $entity_id;
      break;

    case 'page':
      $gallery_id = 'gallery-all';
      break;

    case 'parent':
      $gallery_id = 'gallery-parent-entity-' . $parent_entity_id;
      break;

    case 'paragraph':
      $gallery_id = 'gallery-parent-paragraph-' . $parent_paragraph_id;
      break;

    case 'field_post':
      $gallery_id = 'gallery-' . $entity_id . '-' . $field_name;
      break;

    case 'field_page':
      $gallery_id = 'gallery-' . $field_name;
      break;

    case 'custom':
      $token_service = \Drupal::token();
      $gallery_id = $token_service->replace($settings['glightbox_gallery_custom'], [$entity_type => $entity, 'file' => $item], ['clear' => TRUE]);
      break;

    default:
      $gallery_id = '';
  }

  // If gallery id is not empty add unique per-request token to avoid
  // images being added manually to galleries.
  if (!empty($gallery_id) && $config->get('advanced.unique_token')) {
    // Check if gallery token has already been set, we need to reuse
    // the token for the whole request.
    if (is_null($gallery_token)) {
      // We use a short token since randomness is not critical.
      $gallery_token = Crypt::randomBytesBase64(8);
    }
    $gallery_id = $gallery_id . '-' . $gallery_token;
  }

  // Set up the $variables['image'] parameter.
  if ($settings['display'] == 'text') {
    $variables['image'] = [
      '#markup' => $settings['link_text'],
    ];
  }
  elseif($settings['display']== 'media_title') {
    $variables['image'] = [
      '#markup' => $entity_title,
    ];
  }
  elseif (!empty($settings['image_style'])) {
    $variables['image'] = [
      '#theme' => 'image_style',
      '#style_name' => $settings['image_style'],
    ];
  }
  else {
    $variables['image'] = [
      '#theme' => 'image',
    ];
  }

  if (!empty($variables['image'])) {
    $variables['image']['#attributes'] = [];

    // Do not output an empty 'title' attribute.
    if (mb_strlen($item->title ?? '') != 0) {
      $variables['image']['#title'] = $item->title;
    }

    foreach (['width', 'height', 'alt'] as $key) {
      $variables['image']["#$key"] = $item->$key;
    }

    $variables['image']['#uri'] = empty($item->uri) ? $image_uri : $item->uri;
  }

  // If File Entity module is enabled, load attribute values from file entity.
  if (\Drupal::moduleHandler()->moduleExists('file_entity')) {
    // File id of the save file.
    $fid = $item->target_id;
    // Load file object.
    $file_obj = File::load($fid);
    $file_array = $file_obj->toArray();
    // Populate the image title.
    if (!empty($file_array['field_image_title_text'][0]['value']) && empty($item->title) && $settings['glightbox_caption'] == 'title') {
      $caption = $file_array['field_image_title_text'][0]['value'];
    }
    // Populate the image alt text.
    if (!empty($file_array['field_image_alt_text'][0]['value']) && empty($item->alt) && $settings['glightbox_caption'] == 'alt') {
      $caption = $file_array['field_image_alt_text'][0]['value'];
    }
  }

  if ($caption) {
    $variables['attributes']['title'] = Xss::filter($caption);
  }

  // @todo Make it more common with checking media type by Local Video class.
  if (!empty($variables['entity']) && $variables['entity']->bundle() == 'video') {
    $variables['attributes']['href'] = $variables['entity']
      ->field_media_video_file
      ->referencedEntities()[0]->get('uri')->value;
    $variables['attributes']['href'] = \Drupal::service('file_url_generator')->generateAbsoluteString($variables['attributes']['href']);
  }

  if ($gallery_id) {
    $variables['attributes']['data-gallery'] = $gallery_id;
  }

  $variables['attributes']['class'] = $classes_array;
}

/**
 * Get YouTube video ID.
 *
 * @param string $url
 *
 * @return false|string
 */
function _glightbox_media_video_extract_youtube_video_id($url) {
  // Cases:
  // 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
  // 'https://youtu.be/dQw4w9WgXcQ';
  // 'https://www.youtube.com/embed/dQw4w9WgXcQ';
  // 'https://www.youtube-nocookie.com/embed/Ga6RYejo6Hk';
  // Regular expression pattern to match YouTube video IDs
  $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube-nocookie\.com\/embed\/)([a-zA-Z0-9_-]{11})/';

  // Perform the regular expression match
  if (preg_match($pattern, $url, $matches)) {
    // $matches[1] contains the video ID
    return $matches[1];
  }
  else {
    // URL format not recognized.
    return FALSE;
  }
}

/**
 * Get the caption for the video.
 *
 * @todo this is a duplicate of _glightbox_formatter_get_caption, a lot of the
 * code of the formatters can be unified.
 *
 * @param array $variables
 *   An associative array containing:
 *   - item: An ImageItem object.
 *   - item_attributes: An optional associative array of html attributes to be
 *     placed in the img tag.
 *   - entity: An entity object.
 *   - settings: Formatter settings array.
 * @param string $caption_field
 *   The caption field to use.
 * @param bool $filter
 *   Whether to filter the caption text.
 *
 * @return string
 *   The caption text of the image parameter.
 */
function _glightbox_media_video_formatter_get_caption(array &$variables, string $caption_field = 'glightbox_caption', bool $filter = TRUE): string {
  $item = $variables['thumb'];
  $entity = $variables['entity'];
  $settings = $variables['settings'];

  // Build the caption.
  $entity_title = $entity->label();
  $entity_type = $entity->getEntityTypeId();
  $entity_bundle = $entity->bundle();

  switch ($settings[$caption_field]) {
    case 'auto':
      // If the title is empty use alt or the entity title in that order.
      if (!empty($item->title)) {
        $caption = $item->title;
      }
      elseif (!empty($item->alt)) {
        $caption = $item->alt;
      }
      elseif (!empty($entity_title)) {
        $caption = $entity_title;
      }
      else {
        $caption = '';
      }
      break;

    case 'title':
      $caption = $item->title;
      break;

    case 'alt':
      $caption = $item->alt;
      break;

    case 'entity_title':
      $caption = $entity_title;
      break;

    case 'custom':
      $token_service = \Drupal::token();
      $data = [$entity_type => $entity, 'file' => $item];
      if (!empty($entity->_referringItem)) {
        $parent = $entity->_referringItem->getEntity();
        if ($parent->getEntityTypeId() == 'paragraph') {
          $data['paragraph'] = $parent;
        }
      }
      $caption = $token_service->replace(
        $settings["{$caption_field}_custom"],
        $data,
        ['clear' => TRUE]
      );
      break;

    default:
      $caption = '';
  }

  // If File Entity module is enabled, load attribute values from file entity.
  if (\Drupal::moduleHandler()->moduleExists('file_entity')) {
    // File id of the save file.
    $fid = $item->target_id;
    // Load file object.
    $file_obj = File::load($fid);
    $file_array = $file_obj->toArray();
    // Populate the image title.
    if (!empty($file_array['field_image_title_text'][0]['value']) && empty($item->title) && $settings[$caption_field] == 'title') {
      $caption = $file_array['field_image_title_text'][0]['value'];
    }
    // Populate the image alt text.
    if (!empty($file_array['field_image_alt_text'][0]['value']) && empty($item->alt) && $settings[$caption_field] == 'alt') {
      $caption = $file_array['field_image_alt_text'][0]['value'];
    }
  }

  // Shorten the caption for the example styles or when caption
  // shortening is active.
  $config = \Drupal::config('glightbox.settings');
  $glightbox_style = !empty($config->get('glightbox_style')) ? $config->get('glightbox_style') : '';
  $trim_length = $config->get('glightbox_caption_trim_length');
  if (((strpos($glightbox_style, 'glightbox/example') !== FALSE) || $config->get('glightbox_caption_trim')) && (strlen($caption) > $trim_length)) {
    $caption = substr($caption, 0, $trim_length - 5) . '...';
  }
  if ($filter) {
    return Xss::filter($caption);
  }
  return $caption;
}
