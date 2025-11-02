<?php

namespace Drupal\twig_placeholders\Twig;

use Drupal\Core\Template\Attribute;
use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a custom Twig extension to generate menu data.
 *
 * @type MenuItem array{
 *   attributes: \Drupal\Core\Template\Attribute,
 *   title: array,
 *   url: string,
 *   below: MenuItem[],
 * }
 *
 * @phpstan-type MenuItem array{
 *   attributes: \Drupal\Core\Template\Attribute,
 *   title: array,
 *   url: string,
 *   below: MenuItem[],
 * }
 * @phpstan-ignore typeAlias.circular
 */
class MenuDataExtension extends AbstractExtension {
  /**
   * The Lorem Ipsum generator service.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator $generator;

  /**
   * Constructs a MenuDataExtension object.
   *
   * @param \Drupal\twig_placeholders\Service\LoremIpsumGenerator $generator
   *   The Lorem Ipsum generator service.
   */
  public function __construct(LoremIpsumGenerator $generator) {
    $this->generator = $generator;
  }

  const array DEFAULT_ARRAY_SHAPE = [
    [3, 6],
    [0, 10],
    [0, 8],
  ];

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    /** @var callable $menu_callable */
    $menu_callable = [$this, 'generateMenuData'];

    return [
      new TwigFunction('tp_menu_data', $menu_callable),
    ];
  }

  /**
   * Generates menu data based on the provided shape array.
   *
   * @param string $name
   *   The name of the method to call.
   * @param mixed[] $arguments
   *   The arguments to pass to the method.
   *
   * @return array<string, mixed>
   *   The generated menu data, where each item is an associative array with
   *   keys: 'attributes', 'title', 'url', and 'below'.
   */
  public function __call(string $name, array $arguments): array {
    if ($name !== 'generateMenuData') {
      throw new \BadMethodCallException("Method $name does not exist.");
    }

    if (count($arguments) === 0) {
      return $this->generateFromShapeArray(self::DEFAULT_ARRAY_SHAPE);
    }

    if (
      count($arguments) === 1 &&
      is_array($arguments[0])
    ) {
      $array_shape = $arguments[0];

      $valid = TRUE;
      foreach ($array_shape as $shape) {
        if (!is_array($shape) || count($shape) !== 2) {
          $valid = FALSE;
          $message = 'Invalid shape array provided. Each shape must be an array of two integers.';
          break;
        }
        if (!is_int($shape[0]) || !is_int($shape[1])) {
          $valid = FALSE;
          $message = 'Invalid shape array provided. Each shape must be an array of two integers.';
          break;
        }
        if ($shape[0] < 0 || $shape[1] < 0) {
          $valid = FALSE;
          $message = 'Invalid shape array provided. Each shape must be an array of two non-negative integers.';
          break;
        }
        if ($shape[0] > $shape[1]) {
          $valid = FALSE;
          $message = 'Invalid shape array provided. The first integer must be less than or equal to the second integer.';
          break;
        }
      }

      if ($valid) {
        /** @var array<array{0: int, 1: int}> $array_shape */
        return $this->generateFromShapeArray($array_shape);
      }
    }

    throw new \InvalidArgumentException($message ?? 'Invalid argument provided.');
  }

  /**
   * Generates menu data based on the provided shape array.
   *
   * @param array<array{0: int, 1: int}> $shape_array
   *   The shape array is an array of arrays, where each inner array contains
   *   two integers. The first integer represents the minimum number of children
   *   for that level. The second integer represents the maximum number of
   *   children for that level.
   *
   * @return array<array<string,array<mixed>|Attribute|string>>
   *   The generated menu data.
   */
  public function generateFromShapeArray(array $shape_array): array {
    $level_range = array_shift($shape_array);
    $level_children_count = rand($level_range[0] ?? 0, $level_range[1] ?? 0);

    $menu_items = [];
    for ($i = 0; $i < $level_children_count; ++$i) {
      $children = !empty($shape_array)
        ? $this->generateFromShapeArray($shape_array)
        : [];

      // @todo Replace with better random generated menu data.
      $menu_items[] = [
        'attributes' => new Attribute(),
        'title' => $this->generator->generate(2, NULL, FALSE),
        'url' => 'internal:##',
        'below' => $children,
      ];
    }

    return $menu_items;
  }

}
