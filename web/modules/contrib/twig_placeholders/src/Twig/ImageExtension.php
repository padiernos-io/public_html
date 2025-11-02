<?php

namespace Drupal\twig_placeholders\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for generating placeholder images.
 */
class ImageExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('tp_image', $this->generatePlaceholderImage(...)),
    ];
  }

  /**
   * Generates a placeholder image URL or full <img> tag.
   *
   * @param bool|null $url_only
   *   If true, return only the image URL. Otherwise, return a full <img> tag.
   * @param int|null $width
   *   The image width (default: 800).
   * @param int|null $height
   *   The image height (default: 450).
   * @param int|null $image_id
   *   The specific image ID (optional).
   * @param bool|null $grayscale
   *   If true, apply grayscale effect.
   * @param int|null $blur
   *   Blur level from 1 to 10 (optional).
   * @param string|null $extension
   *   Image format ('webp' or 'jpg', optional).
   *
   * @return string|array<string,mixed>
   *   The generated placeholder image URL or render array for full HTML.
   */
  public function generatePlaceholderImage(
    ?bool $url_only = FALSE,
    ?int $width = 800,
    ?int $height = 450,
    ?int $image_id = NULL,
    ?bool $grayscale = FALSE,
    ?int $blur = NULL,
    ?string $extension = NULL,
  ): string|array {
    // Build the base URL.
    $base_url = "https://picsum.photos";

    // Add image ID if provided.
    if ($image_id !== NULL) {
      $base_url .= "/id/$image_id";
    }

    // Append width and height.
    $base_url .= "/{$width}/{$height}";

    // Build query parameters.
    $params = [];
    if ($grayscale) {
      $params[] = "grayscale";
    }
    if ($blur !== NULL && $blur >= 1 && $blur <= 10) {
      $params[] = "blur=$blur";
    }

    // Append the extension if valid.
    if ($extension !== NULL && in_array($extension, ['webp', 'jpg'])) {
      $base_url .= ".$extension";
    }

    // Append query parameters if any exist.
    if (!empty($params)) {
      $base_url .= '?' . implode('&', $params);
    }

    // If only the URL is requested, return the raw URL.
    if ($url_only) {
      return $base_url;
    }

    // Return a render array instead of a raw HTML string.
    return [
      '#theme' => 'image',
      '#uri' => $base_url,
      '#alt' => 'Placeholder image',
      '#width' => $width,
      '#height' => $height,
    ];
  }

}
