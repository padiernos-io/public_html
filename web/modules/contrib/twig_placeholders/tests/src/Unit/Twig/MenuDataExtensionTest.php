<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use Drupal\twig_placeholders\Twig\MenuDataExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Error\RuntimeError;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\MenuDataExtension
 *
 * @group twig_placeholders
 *
 * @phpstan-import-type MenuItem from \Drupal\twig_placeholders\Twig\MenuDataExtension
 */
class MenuDataExtensionTest extends TestCase {
  /**
   * The Twig environment.
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
   * Sets up the Twig environment and adds the MenuDataExtension.
   *
   * @throws \PHPUnit\Framework\MockObject\Exception
   */
  protected function setUp(): void {
    parent::setUp();

    $loader = new ArrayLoader([
      'test_from_array' => '{{ tp_menu_data(array_shape) | json_encode }}',
    ]);
    $this->twig = new Environment($loader);

    $this->mockGenerator = $this->createMock(LoremIpsumGenerator::class);
    $this->mockGenerator->method('generate')->willReturn(
      ['#markup' => 'Sample Text']
    );

    $extension = new MenuDataExtension($this->mockGenerator);
    $this->twig->addExtension($extension);
  }

  /**
   * Validates the shape of a menu item.
   *
   * @param mixed[] $item
   *   Validates the shape of a menu item.
   */
  protected function validateMenuItem(array $item): void {
    $this->assertArrayHasKey('attributes', $item);
    $this->assertArrayHasKey('title', $item);
    $this->assertIsArray($item['title']);
    $this->assertNotEmpty($item['title']);
    $this->assertArrayHasKey('url', $item);
    $this->assertIsString($item['url']);
    $this->assertArrayHasKey('below', $item);
    $this->assertIsArray($item['below']);
  }

  /**
   * Validates the menu data against the expected shape.
   *
   * @param mixed[] $menu_data
   *   The menu data to validate.
   * @param array<array{0: int, 1: int}> $expected_shape
   *   The expected shape of the menu data.
   */
  protected function validateMenuData(
    array $menu_data,
    array $expected_shape,
  ): void {
    if (empty($menu_data)) {
      return;
    }

    // Get the top level of the expected shape.
    $top_level = array_shift($expected_shape) ?? [0, 0];

    $this->assertLessThanOrEqual($top_level[1], count($menu_data));

    // Check through each item in the menu data, and validate it.
    foreach ($menu_data as $item) {
      if (is_array($item)) {
        $this->validateMenuItem($item);
      }

      $this->assertIsArray($item);
      $this->assertArrayHasKey('below', $item);

      if (
        isset($item['below']) &&
        is_array($item['below'])
      ) {
        $this->validateMenuData(
          menu_data: $item['below'],
          expected_shape: $expected_shape,
        );
      }
    }
  }

  /**
   * Tests the generateMenuData method with a simple array.
   *
   * @throws \Exception
   */
  public function testGenerateMenuDataFromArray(): void {
    $array_shape = [
      [5, 10],
      [2, 4],
      [0, 1],
    ];

    $output = $this->twig->render('test_from_array', [
      'array_shape' => $array_shape,
    ]);
    $output = html_entity_decode($output);
    $decoded_output = json_decode($output, TRUE) ?? [];

    $this->assertIsArray($decoded_output);

    $this->validateMenuData(
      menu_data: $decoded_output,
      expected_shape: $array_shape,
    );
  }

  /**
   * Tests the generateMenuData method with an invalid shape.
   *
   * @throws \Twig\Error\SyntaxError
   * @throws \Twig\Error\LoaderError
   */
  public function testGenerateMenuDataFromArrayWithInvalidShape(): void {
    $this->expectException(RuntimeError::class);

    $array_shape = [
      [5, 10],
      [2, 4],
      [0, -1],
    ];

    $this->twig->render('test_from_array', [
      'array_shape' => $array_shape,
    ]);

    $this->expectException(RuntimeError::class);

    $array_shape = [
      [10, 2],
      [0, 1],
    ];

    $this->twig->render('test_from_array', [
      'array_shape' => $array_shape,
    ]);
  }

  /**
   * Tests the generateMenuData method with an empty array.
   *
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   */
  public function testGenerateMenuDataFromArrayWithEmptyArray(): void {
    $array_shape = [];

    $output = $this->twig->render('test_from_array', [
      'array_shape' => $array_shape,
    ]);
    $output = html_entity_decode($output);
    $decoded_output = json_decode($output, TRUE) ?? [];

    $this->assertIsArray($decoded_output);
  }

  /**
   * Tests the generateMenuData method with a default shape.
   *
   * @throws \Twig\Error\LoaderError
   * @throws \Twig\Error\RuntimeError
   * @throws \Twig\Error\SyntaxError
   */
  public function testGenerateMenuDataFromDefaultShape(): void {
    $output = $this->twig->render('test_from_array', [
      'array_shape' => MenuDataExtension::DEFAULT_ARRAY_SHAPE,
    ]);
    $output = html_entity_decode($output);
    $decoded_output = json_decode($output, TRUE) ?? [];

    $this->assertIsArray($decoded_output);

    $this->validateMenuData(
      menu_data: $decoded_output,
      expected_shape: MenuDataExtension::DEFAULT_ARRAY_SHAPE,
    );
  }

}
