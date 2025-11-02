<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\twig_placeholders\Twig\SelectDataExtension;
use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\SelectDataExtension
 *
 * @group twig_placeholders
 */
class SelectDataExtensionTest extends TestCase {

  /**
   * The Twig environment.
   *
   * @var \Twig\Environment
   */
  protected Environment $twig;

  /**
   * The mocked Lorem Ipsum generator.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator&\PHPUnit\Framework\MockObject\MockObject
   */
  protected LoremIpsumGenerator&MockObject $mockGenerator;

  /**
   * Set up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();
    // Mock the LoremIpsumGenerator service.
    $this->mockGenerator = $this->createMock(LoremIpsumGenerator::class);

    // Create the SelectDataExtension instance with the mocked service.
    $extension = new SelectDataExtension($this->mockGenerator);

    // Set up Twig with the extension.
    $loader = new ArrayLoader([
      'test_template' => '{{ tp_select_data(num_items, num_optgroups, randomise) | json_encode }}',
    ]);

    $this->twig = new Environment($loader);
    $this->twig->addExtension($extension);
  }

  /**
   * Tests the generateSelectData method without optgroups.
   */
  public function testSelectDataWithoutOptgroups(): void {
    // Expected flat list output.
    $expectedOutput = [
      ['type' => 'option', 'value' => 'item_1', 'label' => ['#markup' => 'Option A'], 'selected' => FALSE],
      ['type' => 'option', 'value' => 'item_2', 'label' => ['#markup' => 'Option B'], 'selected' => FALSE],
      ['type' => 'option', 'value' => 'item_3', 'label' => ['#markup' => 'Option C'], 'selected' => FALSE],
    ];

    // Mock label generation for 3 items as arrays now.
    $this->mockGenerator
      ->method('generate')
      ->willReturnOnConsecutiveCalls(
        ['#markup' => 'Option A'],
        ['#markup' => 'Option B'],
        ['#markup' => 'Option C'],
      );

    // Create the SelectDataExtension instance with the mocked service.
    $extension = new SelectDataExtension($this->mockGenerator);

    // Directly test the generateSelectData method (bypassing Twig).
    $actualOutput = $extension->generateSelectData(3, 0, FALSE);

    // Check that the output matches the expected structure.
    $this->assertEquals($expectedOutput, $actualOutput);
  }

  /**
   * Tests the generateSelectData method with optgroups.
   */
  public function testSelectDataWithOptgroups(): void {
    // Expected structured output with optgroups.
    $expectedOutput = [
      [
        'type' => 'optgroup',
        'label' => ['#markup' => 'Group 1'],
        'options' => [
          ['type' => 'option', 'value' => 'item_1', 'label' => ['#markup' => 'Option A'], 'selected' => FALSE],
          ['type' => 'option', 'value' => 'item_2', 'label' => ['#markup' => 'Option B'], 'selected' => FALSE],
        ],
      ],
      [
        'type' => 'optgroup',
        'label' => ['#markup' => 'Group 2'],
        'options' => [
          ['type' => 'option', 'value' => 'item_3', 'label' => ['#markup' => 'Option C'], 'selected' => FALSE],
          ['type' => 'option', 'value' => 'item_4', 'label' => ['#markup' => 'Option D'], 'selected' => FALSE],
        ],
      ],
    ];

    // Mock label generation for 4 items and 2 groups.
    $this->mockGenerator
      ->method('generate')
      ->willReturnOnConsecutiveCalls(
        ['#markup' => 'Option A'],
        ['#markup' => 'Option B'],
        ['#markup' => 'Option C'],
        ['#markup' => 'Option D'],
        ['#markup' => 'Group 1'],
        ['#markup' => 'Group 2'],
      );

    // Create the SelectDataExtension instance with the mocked service.
    $extension = new SelectDataExtension($this->mockGenerator);

    // Directly test the generateSelectData method (bypassing Twig).
    $actualOutput = $extension->generateSelectData(4, 2, FALSE);

    // Check that the output matches the expected structure.
    $this->assertEquals($expectedOutput, $actualOutput);
  }

  /**
   * Tests tp_select_data function with randomisation.
   */
  public function testSelectDataWithRandomisation(): void {
    // Mock label generation for 6 items and 3 groups.
    $this->mockGenerator
      ->method('generate')
      // Return labels for the 6 items and 3 groups.
      ->willReturnOnConsecutiveCalls(
        ['#markup' => 'Option A'],
        ['#markup' => 'Option B'],
        ['#markup' => 'Option C'],
        ['#markup' => 'Option D'],
        ['#markup' => 'Option E'],
        ['#markup' => 'Option F'],
        ['#markup' => 'Group 1'],
        ['#markup' => 'Group 2'],
        ['#markup' => 'Group 3'],
        ['#markup' => 'Option A'],
        ['#markup' => 'Option B'],
        ['#markup' => 'Option C'],
        ['#markup' => 'Option D'],
        ['#markup' => 'Option E'],
        ['#markup' => 'Option F'],
        ['#markup' => 'Group 1'],
        ['#markup' => 'Group 2'],
        ['#markup' => 'Group 3'],
        ['#markup' => 'Option A'],
        ['#markup' => 'Option B'],
        ['#markup' => 'Option C'],
        ['#markup' => 'Option D'],
        ['#markup' => 'Option E'],
        ['#markup' => 'Option F'],
        ['#markup' => 'Group 1'],
        ['#markup' => 'Group 2'],
        ['#markup' => 'Group 3'],
      );

    // Render template with randomisation enabled.
    $output = $this->twig->render(
      'test_template',
      [
        'num_items' => 6,
        'num_optgroups' => 3,
        'randomise' => TRUE,
      ]
    );

    // Decode the HTML entities to prevent issues with encoded characters.
    $decodedOutput = html_entity_decode($output);

    // Now decode the JSON string into an array.
    $actualOutput = json_decode($decodedOutput, TRUE);

    // Assert that the decoded output is an array.
    $this->assertIsArray($actualOutput, 'The first decoded output is not an array.');

    // Assert the output has 3 optgroups.
    $this->assertCount(3, $actualOutput);

    // Assert each optgroup has 'options'.
    foreach ($actualOutput as $optgroup) {
      $this->assertIsArray($optgroup);
      $this->assertArrayHasKey('options', $optgroup);
      $this->assertIsArray($optgroup['options']);
      $this->assertGreaterThan(0, count($optgroup['options']));

      // Check that all options are part of the generated output.
      $expectedOptionLabels = ['Option A', 'Option B', 'Option C', 'Option D', 'Option E', 'Option F'];
      foreach ($optgroup['options'] as $option) {
        $this->assertIsArray($option);
        $this->assertArrayHasKey('label', $option);
        $this->assertIsArray($option['label']);
        $this->assertArrayHasKey('#markup', $option['label']);
        $this->assertIsString($option['label']['#markup']);
        $this->assertContains($option['label']['#markup'], $expectedOptionLabels);
      }
    }

    // Check that all expected labels are present in the actual output (this
    // confirms all options are included, but not in order).
    $allLabels = [];

    foreach ($actualOutput as $optgroup) {
      $this->assertIsArray($optgroup);
      $this->assertIsArray($optgroup['options']);
      foreach ($optgroup['options'] as $option) {
        $this->assertIsArray($option);
        $this->assertArrayHasKey('label', $option);
        $this->assertIsArray($option['label']);
        $this->assertArrayHasKey('#markup', $option['label']);
        $this->assertIsString($option['label']['#markup']);
        $allLabels[] = $option['label']['#markup'];
      }
    }

    // Assert that we have all 6 options in the final output (order doesn't
    // matter).
    $this->assertCount(6, $allLabels);

    sort($allLabels);

    $this->assertEquals(['Option A', 'Option B', 'Option C', 'Option D', 'Option E', 'Option F'], $allLabels);

    // Check that randomisation actually shuffles the results.
    $anotherOutput = json_decode($this->twig->render(
      'test_template',
      [
        'num_items' => 6,
        'num_optgroups' => 3,
        'randomise' => TRUE,
      ]
    ), TRUE);

    // Assert that the outputs are different (to check randomisation).
    $this->assertNotEquals($actualOutput, $anotherOutput, 'The randomisation did not produce different results.');
  }

}
