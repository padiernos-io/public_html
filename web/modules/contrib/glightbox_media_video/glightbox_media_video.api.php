<?php

/**
 * @file
 * Hooks provided by the Lightbox Media Video module.
 */

/**
 * Provides the ability to override Lightbox media video URL.
 *
 * @param string $video_url
 *   Local or remote video URL.
 */
function hook_glightbox_media_video_url_alter(string &$video_url) {
  $youtube_id = _glightbox_media_video_extract_youtube_video_id($video_url);
  if (!empty($youtube_id)) {
    $video_url = 'https://www.youtube-nocookie.com/embed/' . $youtube_id;
  }
}
