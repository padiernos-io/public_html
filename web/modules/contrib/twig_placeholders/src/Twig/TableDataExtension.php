<?php

namespace Drupal\twig_placeholders\Twig;

use Drupal\Core\Template\Attribute;
use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for generating placeholder table data.
 */
class TableDataExtension extends AbstractExtension {
  /**
   * The Lorem Ipsum generator service.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator $generator;

  /**
   * Constructs a TableDataExtension object.
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
      new TwigFunction('tp_table_data', $this->generateTableData(...)),
    ];
  }

  /**
   * Generates placeholder table data.
   *
   * @param int $num_rows
   *   Number of rows (default: 10).
   * @param int $num_cols
   *   Number of columns (default: 4).
   * @param mixed $header_row_opts
   *   - FALSE (default): All cells use <td>.
   *   - TRUE: First row uses <th>, all others use <td>.
   *   - 'td' or 'th': Forces all cells to use the specified element.
   *
   * @return array<array<string, mixed>>|array<array<string, mixed>>
   *   If $header_row_opts is 'th', returns an array of header cells.
   *   Otherwise, returns structured rows.
   */
  public function generateTableData(int $num_rows = 10, int $num_cols = 4, mixed $header_row_opts = FALSE): array {
    // If header is explicitly set to "th", return a flat array of header cells.
    if ($header_row_opts === 'th') {
      $header_cells = [];
      for ($j = 0; $j < $num_cols; $j++) {
        $header_cells[] = [
          'tag' => 'th',
          'attributes' => new Attribute(),
          'content' => $this->generator->generate(2),
        ];
      }
      return $header_cells;
    }

    // Otherwise, return the standard row structure.
    $rows = [];
    for ($i = 0; $i < $num_rows; $i++) {
      $cells = [];
      $is_header_row = $header_row_opts === TRUE && $i === 0;

      for ($j = 0; $j < $num_cols; $j++) {
        $cells[] = [
          'tag' => $is_header_row ? 'th' : 'td',
          'attributes' => new Attribute(),
          'content' => $this->generator->generate(2, NULL, FALSE),
        ];
      }

      $rows[] = [
        'attributes' => new Attribute(),
        'cells' => $cells,
      ];
    }

    return $rows;
  }

}
