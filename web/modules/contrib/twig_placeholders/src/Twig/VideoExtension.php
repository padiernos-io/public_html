<?php

namespace Drupal\twig_placeholders\Twig;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Template\Attribute;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for generating placeholder videos.
 */
class VideoExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('tp_video', $this->generatePlaceholderVideo(...)),
    ];
  }

  public const ALLOWED_VIDEO_IDS = ['Big_Buck_Bunny', 'Jellyfish', 'Sintel'];
  public const ALLOWED_VIDEO_SIZES = ['1080', '720', '360'];
  public const ALLOWED_VIDEO_EXTENSIONS = ['mp4', 'webm', 'mkv'];

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * Constructs a VideoExtension object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('messenger') instanceof MessengerInterface ? $container->get('messenger') : throw new \InvalidArgumentException('The messenger service must implement MessengerInterface.')
    );
  }

  /**
   * Validates if the provided video ID is allowed.
   *
   * @param string $videoId
   *   The video ID to validate.
   *
   * @return bool
   *   TRUE if the video ID is valid, FALSE otherwise.
   */
  public static function isValidVideoId(string $videoId): bool {
    return in_array($videoId, self::ALLOWED_VIDEO_IDS, TRUE);
  }

  /**
   * Validates if the provided video size is allowed.
   *
   * @param string $size
   *   The video size to validate.
   *
   * @return bool
   *   TRUE if the video size is valid, FALSE otherwise.
   */
  public static function isValidVideoSize(string $size): bool {
    return in_array($size, self::ALLOWED_VIDEO_SIZES, TRUE);
  }

  /**
   * Validates if the provided video extension is allowed.
   *
   * @param string $extension
   *   The video extension to validate.
   *
   * @return bool
   *   TRUE if the video extension is valid, FALSE otherwise.
   */
  public static function isValidExtension(string $extension): bool {
    return in_array($extension, self::ALLOWED_VIDEO_EXTENSIONS, TRUE);
  }

  /**
   * Retrieves a random video ID from the ALLOWED_VIDEO_IDS list.
   *
   * @return string
   *   A random video ID.
   */
  protected function getRandomVideoId() {
    return self::ALLOWED_VIDEO_IDS[array_rand(self::ALLOWED_VIDEO_IDS)];
  }

  /**
   * Generates a placeholder video URL or full <video> tag.
   *
   * @param ?bool $url_only
   *   If true, return only the video URL. Otherwise, return a full <video> tag.
   * @param ?string $video_id
   *   Video ID from ['Big_Buck_Bunny', 'Jellyfish', 'Sintel'] (optional).
   * @param ?string $video_size
   *   Filesize from ['1080', '720', '360'] (optional).
   * @param ?string $video_extension
   *   Video format from ['mp4', 'webm', 'mkv'] (optional).
   * @param ?bool $video_autoplay
   *   If true, autoplay the video.
   * @param ?bool $video_controls
   *   If true, show video controls.
   * @param ?bool $video_loop
   *   If true, loop the video.
   * @param ?bool $video_muted
   *   If true, mute the video.
   * @param ?bool $video_playsinline
   *   If true, play the video inline.
   *
   * @return string|array<string,mixed>
   *   The generated placeholder video URL or render array for full HTML.
   */
  public function generatePlaceholderVideo(
    ?bool $url_only = FALSE,
    ?string $video_id = NULL,
    ?string $video_size = '1080',
    ?string $video_extension = 'mp4',
    ?bool $video_autoplay = FALSE,
    ?bool $video_controls = TRUE,
    ?bool $video_loop = FALSE,
    ?bool $video_muted = FALSE,
    ?bool $video_playsinline = TRUE,
  ): string|array {
    // Set default values for NULL inputs.
    $video_id = $video_id ?? $this->getRandomVideoId();
    $video_size = $video_size ?? '1080';
    $video_extension = $video_extension ?? 'mp4';

    // Validate parameters and add warning messages with allowed values.
    if (!self::isValidVideoId($video_id)) {
      $allowed_ids = implode(', ', self::ALLOWED_VIDEO_IDS);

      $this->messenger->addWarning(sprintf(
        'Invalid video ID "%s" provided to tp_video(). Falling back to a random video. Allowed IDs: %s.',
        $video_id,
        $allowed_ids
      ));

      $video_id = $this->getRandomVideoId();
    }

    if (!self::isValidVideoSize($video_size)) {
      $allowed_sizes = implode(', ', self::ALLOWED_VIDEO_SIZES);

      $this->messenger->addWarning(sprintf(
        'Invalid video size "%s" provided to tp_video(). Falling back to default size of 1080. Allowed sizes: %s.',
        $video_size,
        $allowed_sizes
      ));

      $video_size = '1080';
    }

    if (!self::isValidExtension($video_extension)) {
      $allowed_extensions = implode(', ', self::ALLOWED_VIDEO_EXTENSIONS);

      $this->messenger->addWarning(sprintf(
        'Invalid video extension "%s" provided to tp_video(). Falling back to default extension of mp4. Allowed extensions: %s.',
        $video_extension,
        $allowed_extensions
      ));

      $video_extension = 'mp4';
    }

    // Build the base URL.
    $base_url = "https://test-videos.co.uk/vids";

    $codec_video_extension = $video_extension;

    // If video extension is not provided or mp4 is provided,
    // set the additional URL codec parameter to h264.
    if ($video_extension === 'mp4') {
      $codec_video_extension = 'mp4/h264';
    }

    // Build full URL following the pattern established by test-videos.co.uk.
    // For example:
    // https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/720/Big_Buck_Bunny_720_10s_1MB.mp4
    // https://test-videos.co.uk/vids/sintel/mp4/h264/720/Sintel_720_10s_1MB.mp4
    $base_url .= '/' . strtolower(str_replace('_', '', $video_id)) . '/' . $codec_video_extension . '/' . $video_size . '/' . $video_id . '_' . $video_size . '_10s_1MB.' . $video_extension;

    if ($url_only) {
      return $base_url;
    }

    // Render array structure.
    return [
      '#theme' => 'twig_placeholders_video',
      '#attributes' => [
        'width' => round((int) $video_size * (16 / 9)),
        'height' => (int) $video_size,
        'autoplay' => $video_autoplay ? 'autoplay' : FALSE,
        'controls' => $video_controls ? 'controls' : FALSE,
        'loop' => $video_loop ? 'loop' : FALSE,
        'muted' => $video_muted ? 'muted' : FALSE,
        'playsinline' => $video_playsinline ? 'playsinline' : FALSE,
      ],
      '#files' => [
        [
          'source_attributes' => new Attribute([
            'src' => $base_url,
            'type' => 'video/' . $video_extension,
          ]),
        ],
      ],
    ];
  }

}
