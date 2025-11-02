<?php

namespace Drupal\twig_placeholders\Service;

/**
 * Generates simple Lorem Ipsum text.
 */
class LoremIpsumGenerator {

  /**
   * Predefined list of Latin words.
   *
   * @var string[]
   */
  protected array $words = [
    'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'ac',
    'proin', 'accumsan', 'sapien', 'nec', 'massa', 'volutpat', 'venenatis', 'a',
    'purus', 'lacinia', 'pretium', 'quis', 'congue', 'praesent', 'risus', 'dui',
    'sagittis', 'laoreet', 'auctor', 'mauris', 'non', 'velit', 'eros', 'dictum',
    'elit', 'curabitur', 'duis', 'hendrerit', 'libero', 'eleifend', 'facilisis',
    'nunc', 'ornare', 'odio', 'orci', 'gravida', 'imperdiet', 'nullam', 'netus',
    'sed', 'eu', 'molestie', 'lacus', 'quisque', 'porttitor', 'diam', 'blandit',
    'mollis', 'tempus', 'at', 'magna', 'vestibulum', 'turpis', 'ligula', 'cras',
    'tincidunt', 'id', 'condimentum', 'enim', 'sodales', 'in', 'hac', 'aliquet',
    'interdum', 'tellus', 'malesuada', 'rhoncus', 'luctus', 'ante', 'fringilla',
    'cursus', 'aliquam', 'quam', 'dapibus', 'nisl', 'feugiat', 'egestas', 'per',
    'class', 'aptent', 'taciti', 'sociosqu', 'ad', 'litora', 'torquent', 'nibh',
    'et', 'nam', 'suspendisse', 'potenti', 'vivamus', 'sem', 'porta', 'viverra',
    'leo', 'eget', 'semper', 'mattis', 'tortor', 'scelerisque', 'nulla', 'nisi',
    'conubia', 'nostra', 'inceptos', 'himenaeos', 'phasellus', 'lobortis', 'ut',
    'pulvinar', 'vitae', 'urna', 'iaculis', 'metus', 'commodo', 'erat', 'fames',
    'pharetra', 'vulputate', 'maecenas', 'est', 'morbi', 'pellentesque', 'arcu',
    'sollicitudin', 'rutrum', 'bibendum', 'vel', 'lectus', 'tempor', 'dictumst',
    'donec', 'justo', 'vehicula', 'ultricies', 'varius', 'fermentum', 'integer',
    'primis', 'ultrices', 'posuere', 'cubilia', 'etiam', 'convallis', 'euismod',
    'suscipit', 'habitant', 'faucibus', 'consequat', 'aenean', 'neque', 'fusce',
    'habitasse', 'felis', 'augue', 'elementum', 'placerat', 'senectus', 'curae',
    'tristique', 'ullamcorper', 'mi', 'platea',
  ];

  /**
   * Generates Lorem Ipsum text.
   *
   * @param int $word_count
   *   The total number of words to generate (optional).
   * @param int|null $para_count
   *   The number of paragraphs to spread the generated words over (optional).
   * @param bool $punctuate
   *   Whether to add punctuation to the sentences. (optional).
   *
   * @return array<string,mixed>
   *   Generated Lorem Ipsum text in a render array.
   */
  public function generate(int $word_count = 20, ?int $para_count = NULL, bool $punctuate = TRUE): array {
    // Get a random number of words from the list.
    $shuffled_words = $this->words;
    shuffle($shuffled_words);
    $words = array_slice($shuffled_words, 0, $word_count);

    // Create sentences ensuring no words are lost.
    $sentences = [];
    while (count($words) > 0) {
      $remaining_words = count($words);
      $sentence_length = min($remaining_words, rand(8, min(12, $remaining_words)));

      // Form the sentence.
      $sentence_words = array_splice($words, 0, $sentence_length);

      // Capitalise first word and add full stop.
      $sentence_words[0] = ucfirst($sentence_words[0]);

      if ($punctuate) {
        $sentences[] = implode(' ', $sentence_words) . '.';
      }
      else {
        $sentences[] = implode(' ', $sentence_words);
      }
    }

    if ($para_count === NULL) {
      $markup = implode(' ', $sentences);
    }
    else {
      $para_count = max(1, min($para_count, count($sentences)));
      $chunk_size = max(1, (int) ceil(count($sentences) / $para_count));
      $paragraphs = array_filter(array_chunk($sentences, $chunk_size));
      $markup_array = array_map(fn($p) => '<p>' . implode(' ', $p) . '</p>', $paragraphs);
      $markup = implode('', $markup_array);
    }

    return [
      '#markup' => $markup,
    ];
  }

}
