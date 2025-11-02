<?php

namespace Drupal\twig_placeholders\Twig;

use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for generating placeholder select data.
 */
class SelectDataExtension extends AbstractExtension {
  /**
   * The Lorem Ipsum generator service.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator $generator;

  /**
   * Constructs a SelectDataExtension object.
   *
   * @param \Drupal\twig_placeholders\Service\LoremIpsumGenerator $generator
   *   The Lorem Ipsum generator service.
   */
  public function __construct(LoremIpsumGenerator $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('tp_select_data', $this->generateSelectData(...)),
    ];
  }

  /**
   * Generates placeholder select data.
   *
   * @param int $num_items
   *   Total number of items (default: 10).
   * @param int $num_optgroups
   *   Number of optgroups (default: 0).
   * @param bool $randomise
   *   Whether to randomly distribute items into optgroups (default: FALSE).
   *
   * @return array<array<string, mixed>>
   *   Structured array of select options.
   */
  public function generateSelectData(int $num_items = 10, int $num_optgroups = 0, bool $randomise = FALSE): array {
    // Ensure there are enough options for the number of optgroups.
    if ($num_optgroups > $num_items) {
      throw new \InvalidArgumentException('The number of optgroups cannot be greater than the number of options.');
    }

    $options = [];
    $all_items = [];

    // Generate all items first.
    for ($i = 0; $i < $num_items; $i++) {
      $all_items[] = [
        'type' => 'option',
        'value' => 'item_' . ($i + 1),
        'label' => $this->generator->generate(2, NULL, FALSE),
        'selected' => FALSE,
      ];
    }

    // If no optgroups, return a flat list.
    if ($num_optgroups === 0) {
      return $all_items;
    }

    // Distribute items into optgroups.
    if ($randomise) {
      $grouped_items = array_fill(0, $num_optgroups, []);

      // Shuffle items before assignment.
      shuffle($all_items);

      // Step 1: Give each optgroup at least one item.
      for ($i = 0; $i < $num_optgroups && !empty($all_items); $i++) {
        $grouped_items[$i][] = array_pop($all_items);
      }

      // Step 2: Randomly distribute remaining items.
      foreach ($all_items as $item) {
        $random_group = rand(0, $num_optgroups - 1);
        $grouped_items[$random_group][] = $item;
      }
    }
    else {
      $chunk_size = max(1, (int) ceil($num_items / max(1, $num_optgroups)));
      $grouped_items = array_chunk($all_items, $chunk_size);
    }

    for ($i = 0; $i < $num_optgroups; $i++) {
      $options[] = [
        'type' => 'optgroup',
        'label' => $this->generator->generate(2, NULL, FALSE),
        'options' => $grouped_items[$i] ?? [],
      ];
    }

    return $options;

  }

}
