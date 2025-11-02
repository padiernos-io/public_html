<?php

namespace Drupal\opcachectl\Twig\Extension;

use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extensions to format byte counts.
 */
class FormatSize extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter('format_size', $this->formatByteSize(...)),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'format_size';
  }

  /**
   * Format byte size in human-readable format.
   *
   * @param float|int $size
   *   Bytes.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Formatted string.
   */
  public function formatByteSize($size): TranslatableMarkup {
    return ByteSizeMarkup::create($size ?? 0);
  }

}
