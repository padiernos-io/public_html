<?php

namespace Drupal\footnotes\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Fallback for the Twig spaceless deprecation.
 *
 * This is needed because Footnotes truly does need to strip any whitespace
 * around html tags to avoid extra spaces before and after inline citations.
 */
class FootnotesSpacelessTwig extends AbstractExtension {

  /**
   * Returns an array of twig filters to be added.
   *
   * @return array
   *   The twig filter for footnotes spaceless.
   */
  public function getFilters(): array {
    return [
      new TwigFilter(
        'footnotes_spaceless',
        [self::class, 'footnotesSpaceless'],
        ['is_safe' => ['html']],
      ),
    ];
  }

  /**
   * Removes whitespaces between HTML tags.
   *
   * @param string|null $content
   *   The content to remove whitespaces from.
   *
   * @return string
   *   The duplication of the spaceless function prior to Twig 3.12.
   */
  public static function footnotesSpaceless(?string $content): string {
    return trim(preg_replace('/>\s+</', '><', $content ?? ''));
  }

}
