<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\Core\Template\Attribute;
use Drupal\twig_placeholders\Twig\TableDataExtension;
use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\TableDataExtension
 *
 * @group twig_placeholders
 */
class TableDataExtensionTest extends TestCase {

  /**
   * The Twig environment used for rendering templates.
   *
   * @var \Twig\Environment
   */
  protected Environment $twig;

  /**
   * The mock Lorem Ipsum generator service.
   *
   * @var \PHPUnit\Framework\MockObject\MockObject&\Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator&MockObject $mockGenerator;

  /**
   * Sets up the test environment before each test.
   */
  protected function setUp(): void {
    parent::setUp();
    $this->mockGenerator = $this->createMock(LoremIpsumGenerator::class);
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $loader = new ArrayLoader([
      'test_template' => '{{ tp_table_data(num_rows, num_cols) | json_encode }}',
    ]);
    $this->twig = new Environment($loader);
    $this->twig->addExtension($extension);
  }

  /**
   * Tests the default behavior of the generateTableData method.
   */
  public function testGenerateTableDataDefaults(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $actualOutput = $extension->generateTableData();
    $this->assertCount(10, $actualOutput);

    foreach ($actualOutput as $row) {
      assert(is_array($row));
      $this->assertArrayHasKey('attributes', $row);
      $this->assertInstanceOf(Attribute::class, $row['attributes']);
      $this->assertArrayHasKey('cells', $row);
      assert(is_array($row['cells']));

      foreach ($row['cells'] as $cell) {
        assert(is_array($cell));
        $this->assertArrayHasKey('tag', $cell);
        $this->assertEquals('td', $cell['tag']);
        $this->assertArrayHasKey('attributes', $cell);
        $this->assertInstanceOf(Attribute::class, $cell['attributes']);
        $this->assertArrayHasKey('content', $cell);
        $this->assertIsArray($cell['content']);
        $this->assertArrayHasKey('#markup', $cell['content']);
        $this->assertIsString($cell['content']['#markup']);
        $this->assertEquals('Sample Text', $cell['content']['#markup']);
      }
    }
  }

  /**
   * Tests the generateTableData method with custom row and column values.
   */
  public function testGenerateTableDataCustomValues(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $actualOutput = $extension->generateTableData(5, 3);
    $this->assertCount(5, $actualOutput);

    foreach ($actualOutput as $row) {
      assert(is_array($row));
      $this->assertArrayHasKey('attributes', $row);
      $this->assertInstanceOf(Attribute::class, $row['attributes']);
      $this->assertArrayHasKey('cells', $row);
      assert(is_array($row['cells']));

      foreach ($row['cells'] as $cell) {
        assert(is_array($cell));
        $this->assertArrayHasKey('tag', $cell);
        $this->assertEquals('td', $cell['tag']);
        $this->assertArrayHasKey('attributes', $cell);
        $this->assertInstanceOf(Attribute::class, $cell['attributes']);
        $this->assertArrayHasKey('content', $cell);
        $this->assertIsArray($cell['content']);
        $this->assertArrayHasKey('#markup', $cell['content']);
        $this->assertIsString($cell['content']['#markup']);
        $this->assertEquals('Sample Text', $cell['content']['#markup']);
      }
    }
  }

  /**
   * Tests the generateTableData method with the header row set to TRUE.
   */
  public function testGenerateTableDataWithHeaderRow(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $actualOutput = $extension->generateTableData(5, 3, TRUE);
    $this->assertCount(5, $actualOutput);

    // Verify first row has 'th' tags and others have 'td' tags.
    $this->assertIsArray($actualOutput[0]);
    $this->assertArrayHasKey('cells', $actualOutput[0]);
    $this->assertIsArray($actualOutput[1]);
    $this->assertArrayHasKey('cells', $actualOutput[1]);
    $this->assertIsArray($actualOutput[2]);
    $this->assertArrayHasKey('cells', $actualOutput[2]);

    $this->assertIsArray($actualOutput[0]['cells']);
    $this->assertIsArray($actualOutput[1]['cells']);
    $this->assertIsArray($actualOutput[2]['cells']);

    $this->assertIsArray($actualOutput[0]['cells'][0]);
    $this->assertIsArray($actualOutput[0]['cells'][1]);
    $this->assertIsArray($actualOutput[0]['cells'][2]);
    $this->assertIsArray($actualOutput[1]['cells'][0]);
    $this->assertIsArray($actualOutput[1]['cells'][1]);
    $this->assertIsArray($actualOutput[1]['cells'][2]);
    $this->assertIsArray($actualOutput[2]['cells'][0]);
    $this->assertIsArray($actualOutput[2]['cells'][1]);
    $this->assertIsArray($actualOutput[2]['cells'][2]);

    $this->assertArrayHasKey('tag', $actualOutput[0]['cells'][0]);
    $this->assertArrayHasKey('tag', $actualOutput[0]['cells'][1]);
    $this->assertArrayHasKey('tag', $actualOutput[0]['cells'][2]);
    $this->assertArrayHasKey('tag', $actualOutput[1]['cells'][0]);
    $this->assertArrayHasKey('tag', $actualOutput[1]['cells'][1]);
    $this->assertArrayHasKey('tag', $actualOutput[1]['cells'][2]);
    $this->assertArrayHasKey('tag', $actualOutput[2]['cells'][0]);
    $this->assertArrayHasKey('tag', $actualOutput[2]['cells'][1]);
    $this->assertArrayHasKey('tag', $actualOutput[2]['cells'][2]);

    $this->assertIsString($actualOutput[0]['cells'][0]['tag']);
    $this->assertIsString($actualOutput[0]['cells'][1]['tag']);
    $this->assertIsString($actualOutput[0]['cells'][2]['tag']);
    $this->assertIsString($actualOutput[1]['cells'][0]['tag']);
    $this->assertIsString($actualOutput[1]['cells'][1]['tag']);
    $this->assertIsString($actualOutput[1]['cells'][2]['tag']);
    $this->assertIsString($actualOutput[2]['cells'][0]['tag']);
    $this->assertIsString($actualOutput[2]['cells'][1]['tag']);
    $this->assertIsString($actualOutput[2]['cells'][2]['tag']);

    $this->assertEquals('th', $actualOutput[0]['cells'][0]['tag']);
    $this->assertEquals('th', $actualOutput[0]['cells'][1]['tag']);
    $this->assertEquals('th', $actualOutput[0]['cells'][2]['tag']);
    $this->assertEquals('td', $actualOutput[1]['cells'][0]['tag']);
    $this->assertEquals('td', $actualOutput[1]['cells'][1]['tag']);
    $this->assertEquals('td', $actualOutput[1]['cells'][2]['tag']);
  }

  /**
   * Tests the generateTableData method with all rows as header cells ('th').
   */
  public function testGenerateTableDataWithAllHeaders(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $actualOutput = $extension->generateTableData(3, 2, 'th');
    $this->assertCount(2, $actualOutput);

    foreach ($actualOutput as $row) {
      $this->assertIsArray($row);
      $this->assertArrayHasKey('tag', $row);
      $this->assertArrayHasKey('attributes', $row);
      $this->assertArrayHasKey('content', $row);
      $this->assertEquals('th', $row['tag']);
    }
  }

  /**
   * Tests the generateTableData method with all cells set to 'td'.
   */
  public function testGenerateTableDataWithAllCellsAsTd(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $extension = new TableDataExtension($this->mockGenerator);
    $actualOutput = $extension->generateTableData(3, 3, 'td');
    $this->assertCount(3, $actualOutput);

    foreach ($actualOutput as $row) {
      $this->assertIsArray($row);
      $this->assertArrayHasKey('cells', $row);
      $this->assertIsArray($row['cells']);

      foreach ($row['cells'] as $cell) {
        $this->assertIsArray($cell);
        $this->assertArrayHasKey('tag', $cell);
        $this->assertIsString($cell['tag']);
        $this->assertEquals('td', $cell['tag']);
      }
    }
  }

  /**
   * Tests the integration of the Twig function with the template engine.
   */
  public function testTwigFunctionIntegration(): void {
    $this->mockGenerator->method('generate')->willReturn(['#markup' => 'Sample Text']);
    $output = $this->twig->render('test_template', ['num_rows' => 2, 'num_cols' => 2]);
    $decodedOutput = html_entity_decode($output);
    $actualOutput = json_decode($decodedOutput, TRUE) ?? [];
    assert(is_array($actualOutput));

    foreach ($actualOutput as $row) {
      assert(is_array($row));
      $this->assertArrayHasKey('cells', $row);
      assert(is_array($row['cells']));

      foreach ($row['cells'] as $cell) {
        assert(is_array($cell));
        $this->assertArrayHasKey('content', $cell);
        $this->assertIsArray($cell['content']);
        $this->assertArrayHasKey('#markup', $cell['content']);
        $this->assertIsString($cell['content']['#markup']);
      }
    }

    $this->assertCount(2, $actualOutput);
    $this->assertIsArray($actualOutput[0]);
    $this->assertArrayHasKey('cells', $actualOutput[0]);
    $this->assertIsArray($actualOutput[0]['cells']);
    $this->assertCount(2, $actualOutput[0]['cells']);
    $this->assertIsArray($actualOutput[0]['cells'][0]);
    $this->assertArrayHasKey('content', $actualOutput[0]['cells'][0]);
    $this->assertIsArray($actualOutput[0]['cells'][0]['content']);
    $this->assertArrayHasKey('#markup', $actualOutput[0]['cells'][0]['content']);
    $this->assertIsString($actualOutput[0]['cells'][0]['content']['#markup']);
    $this->assertEquals('Sample Text', $actualOutput[0]['cells'][0]['content']['#markup']);
  }

}
