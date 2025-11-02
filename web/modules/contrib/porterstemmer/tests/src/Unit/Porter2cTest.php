<?php

namespace Drupal\Tests\porterstemmer\Unit;

use Drupal\porterstemmer\Porter2;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the "PorterStemmer" implementation.
 *
 * @coversDefaultClass \Drupal\porterstemmer\Porter2
 * @group porterstemmer
 *
 * @see \Drupal\porterstemmer\Porter2
 */
class Porter2cTest extends UnitTestCase {

  use TestItemsTrait;

  /**
   * Test Porter2::stem() with a data provider method.
   *
   * Uses the data provider method to test with a wide range of words/stems.
   *
   * @dataProvider stemDataProvider
   */
  public function testStem($word, $stem): void {
    $this->assertEquals($stem, Porter2::stem($word));
  }

  /**
   * Data provider for testStem().
   *
   * @return array
   *   Nested arrays of values to check:
   *   - $word
   *   - $stem
   */
  public function stemDataProvider() {
    return $this->retrieveStemWords(10000, 5000);
  }

}
