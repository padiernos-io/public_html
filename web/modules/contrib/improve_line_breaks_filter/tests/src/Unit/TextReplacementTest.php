<?php

namespace Drupal\Tests\improve_line_breaks_filter\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\improve_line_breaks_filter\TextReplacement;

/**
 * @coversDefaultClass \Drupal\improve_line_breaks_filter\TextReplacement
 * @group improve_line_breaks_filter
 */
class TextReplacementTest extends UnitTestCase {

  /**
   * Tests replacing empty paragraphs with line breaks tags.
   *
   * @param string $text
   *   The source text.
   * @param string $expected
   *   The expected text.
   * @param bool $remove_empty_paragraphs
   *   Determines whether empty paragraphs will be deleted or replaced.
   *
   * @dataProvider providerProcessImproveLineBreaks
   */
  public function testProcessImproveLineBreaks($text, $expected, $remove_empty_paragraphs = FALSE) {
    $replacement = new TextReplacement();
    $this->assertEquals($expected, $replacement->processImproveLineBreaks($text, $remove_empty_paragraphs));
  }

  /**
   * Provides data for self::testProcessImproveLineBreaks().
   *
   * @return array
   *   An array of test data.
   */
  public function providerProcessImproveLineBreaks() {
    return [
      // Tests replacing empty paragraphs with break line tags.
      [
        '<p>Do not replace&nbsp;</p><p>&nbsp;</p><p>x</p>',
        '<p>Do not replace&nbsp;</p><br /><p>x</p>',
      ],
      [
        '<p></p>',
        '<br />',
      ],
      [
        '<p> </p>',
        '<br />',
      ],
      [
        '<p>&nbsp;</p>',
        '<br />',
      ],
      [
        '<p>&nbsp;&nbsp;</p>',
        '<br />',
      ],
      [
        '<p></p><p></p>',
        '<br /><br />',
      ],
      // Tests removing empty paragraphs.
      [
        '<p>Do not replace&nbsp;</p><p>&nbsp;</p><p>x</p>',
        '<p>Do not replace&nbsp;</p><p>x</p>',
        TRUE,
      ],
      [
        '<p></p>',
        '',
        TRUE,
      ],
      [
        '<p> </p>',
        '',
        TRUE,
      ],
      [
        '<p>&nbsp;</p>',
        '',
        TRUE,
      ],
      [
        '<p>&nbsp;&nbsp;</p>',
        '',
        TRUE,
      ],
      [
        '<p></p><p></p>',
        '',
        TRUE,
      ],
      // Case insensitive.
      [
        '<P></P>',
        '<br />',
      ],
      [
        '<P></P>',
        '',
        TRUE,
      ],
      // Skip ignored tags.
      [
        '<script>var html ="<p>&nbsp;</p>";</script>',
        '<script>var html ="<p>&nbsp;</p>";</script>',
      ],
      [
        '<code><p>&nbsp;</p></code>',
        '<code><p>&nbsp;</p></code>',
      ],
      [
        '<script>var html ="<p>&nbsp;</p>";</script>',
        '<script>var html ="<p>&nbsp;</p>";</script>',
        TRUE,
      ],
      [
        '<code><p>&nbsp;</p></code>',
        '<code><p>&nbsp;</p></code>',
        TRUE,
      ],
    ];
  }

}
