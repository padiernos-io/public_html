<?php

namespace Drupal\Tests\shy\Kernel;

use Drupal\filter\FilterPluginCollection;
use Drupal\filter\FilterProcessResult;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\shy\Plugin\Filter\ShyCleanerFilter
 *
 * @group shy
 */
class ShyCleanerFilterTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['system', 'filter', 'shy'];

  /**
   * Collection of CKeditor Plugin filters.
   *
   * @var \Drupal\filter\Plugin\FilterInterface[]
   */
  protected $filters;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);

    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $this->filters = $bag->getAll();
  }

  /**
   * @covers ::process
   *
   * @dataProvider providerTexts
   */
  public function testCleanerFilter($input, $expected) {
    $filter = $this->filters['shy_cleaner_filter'];

    /** @var \Drupal\filter\FilterProcessResult $result */
    $result = $filter->process($input, 'und');
    $this->assertInstanceOf(FilterProcessResult::class, $result);
    $this->assertEquals($expected, $result->getProcessedText());
  }

  /**
   * Provides texts to check and expected results.
   */
  public function providerTexts() {
    return [
      ['', ''],
      ['<p>Maecenas cursus posuere</p>', '<p>Maecenas cursus posuere</p>'],
      [
        '<p>Maecenas<shy>&shy;</shy>cursus posuere</p>',
        '<p>Maecenas cursus posuere</p>',
      ],
      [
        '<p>Maecenas<shy>&shy;</shy>cursus<shy>&shy;</shy>posuere</p>',
        '<p>Maecenas cursus posuere</p>',
      ],
      [
        '<p>Maecenas<span>&shy;</span>cursus posuere</p>',
        '<p>Maecenas<span> </span>cursus posuere</p>',
      ],
    ];
  }

}
