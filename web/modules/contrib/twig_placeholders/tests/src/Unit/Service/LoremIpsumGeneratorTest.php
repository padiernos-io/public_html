<?php

namespace Drupal\Tests\twig_placeholders\Unit\Service;

use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Lorem Ipsum Generator service.
 *
 * @group twig_placeholders
 */
class LoremIpsumGeneratorTest extends TestCase {

  /**
   * The Lorem Ipsum generator.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator $loremIpsumGenerator;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->loremIpsumGenerator = new LoremIpsumGenerator();
  }

  /**
   * Test the generation of Lorem Ipsum text with default parameters.
   */
  public function testGenerateDefault(): void {
    $result = $this->loremIpsumGenerator->generate();
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertGreaterThanOrEqual(20, str_word_count(strip_tags($result['#markup'])));
  }

  /**
   * Test generation with a small word count (3 words).
   */
  public function testGenerateThreeWords(): void {
    $result = $this->loremIpsumGenerator->generate(3);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertGreaterThanOrEqual(3, str_word_count(strip_tags($result['#markup'])));
  }

  /**
   * Test generation with a large word count (100 words).
   */
  public function testGenerateHundredWords(): void {
    $result = $this->loremIpsumGenerator->generate(100);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertGreaterThanOrEqual(100, str_word_count(strip_tags($result['#markup'])));
  }

  /**
   * Test generation with more paragraphs than words.
   */
  public function testGenerateMoreParagraphsThanWords(): void {
    $result = $this->loremIpsumGenerator->generate(10, 20);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertStringContainsString('<p>', $result['#markup']);
  }

  /**
   * Test generation with 100 words split into 3 paragraphs.
   */
  public function testGenerateHundredWordsThreeParagraphs(): void {
    $result = $this->loremIpsumGenerator->generate(100, 3);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertStringContainsString('<p>', $result['#markup']);
    $this->assertGreaterThanOrEqual(3, substr_count($result['#markup'], '<p>'));
  }

  /**
   * Test that the first word is capitalized.
   */
  public function testFirstWordCapitalization(): void {
    $result = $this->loremIpsumGenerator->generate();
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $text = strip_tags($result['#markup']);
    $words = explode(' ', $text);
    $this->assertEquals(ucfirst($words[0]), $words[0]);
  }

  /**
   * Test that all sentences end with a full stop.
   */
  public function testSentencesEndWithFullStop(): void {
    $result = $this->loremIpsumGenerator->generate(50);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);

    $text = strip_tags($result['#markup']);

    // Split on full stops, ensuring to account for new lines and spaces.
    $sentences = preg_split('/(?<=\.)\s+/', trim($text));

    // Ensure sentences is an array before proceeding.
    $this->assertIsArray($sentences);

    foreach ($sentences as $sentence) {
      $this->assertStringEndsWith(
        '.',
        trim($sentence),
        'Sentence does not end with a full stop: ' . $sentence
      );
    }
  }

  /**
   * Test generation with punctuation disabled.
   */
  public function testGenerateWithoutPunctuation(): void {
    $result = $this->loremIpsumGenerator->generate(50, NULL, FALSE);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);

    $text = strip_tags($result['#markup']);

    // Assert there are no full stops in the text.
    $this->assertDoesNotMatchRegularExpression('/\./', $text, 'Text should not contain any full stops.');
  }

  /**
   * Test generation with zero words (edge case).
   */
  public function testGenerateZeroWords(): void {
    $result = $this->loremIpsumGenerator->generate(0);
    $this->assertArrayHasKey('#markup', $result);
    $this->assertIsString($result['#markup']);
    $this->assertEmpty(strip_tags($result['#markup']));
  }

}
