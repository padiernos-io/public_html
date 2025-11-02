<?php

declare(strict_types=1);

namespace Drupal\shy\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * SHY Cleaner Filter class. Implements process() method only.
 *
 * @Filter(
 *   id = "shy_cleaner_filter",
 *   title = @Translation("Cleanup SHY markup"),
 *   description = @Translation("Replaces <code>&lt;shy&gt;&lt;/shy&gt;</code> tag with a soft hyphen."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 * )
 */
class ShyCleanerFilter extends FilterBase {

  /**
   * The shy character in UFT-8.
   */
  const UTF_8_SHY = "\xc2\xaD";


  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $filtered = $this->swapShyHtml($text);
    if ($filtered) {
      $result = new FilterProcessResult($filtered);
    }
    else {
      $result = new FilterProcessResult($text);
    }

    return $result;
  }

  /**
   * Replace <span class="shy"> and <shy></shy> tags with respected HTML.
   *
   * The previous tag (span.shy) is still on the filter to keep
   * compatibility with previous content created.
   *
   * @param string $text
   *   The HTML string to replace <span class="shy"> and <shy></shy> tags.
   *
   * @return string
   *   The HTML with all the <span class="shy"> and <shy></shy>
   *   tags replaced with their respected html.
   */
  protected function swapShyHtml($text) {
    $document = Html::load($text);
    $xpath = new \DOMXPath($document);

    foreach ($xpath->query('//shy') as $node) {
      if (!empty($node)) {
        // PHP DOM replacing the shy-tag with shy character.
        $node->parentNode->replaceChild(new \DOMText(self::UTF_8_SHY), $node);
      }
    }
    return Html::serialize($document);

  }
}
