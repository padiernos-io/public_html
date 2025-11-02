<?php

namespace Drupal\Tests\porterstemmer\Unit;

/**
 * Tests the "PorterStemmer" implementation with PECL stem_english().
 *
 * @group porterstemmer
 *
 * @see https://pecl.php.net/package/stem
 */
class Porter2Pecl1Test extends PorterPeclBase {

  /**
   * Test PECL stem_english() with a data provider method.
   *
   * Uses the data provider method to test with a wide range of words/stems.
   *
   * @dataProvider stemDataProvider
   */
  public function testStem($word, $stem): void {
    if ($this->hasPeclStem) {
      /* @phpstan-ignore-next-line */
      $this->assertEquals($stem, stem_english($word));
    }
    else {
      $this->markTestSkipped('No PECL stem library found, Aborting test.');
    }
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
    if ($this->hasPeclStem) {
      return $this->retrieveStemWords(0);
    }
    else {
      return [['', '']];
    }
  }

}
