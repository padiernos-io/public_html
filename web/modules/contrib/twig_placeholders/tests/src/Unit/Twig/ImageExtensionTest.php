<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\twig_placeholders\Twig\ImageExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\ImageExtension
 *
 * @group twig_placeholders
 */
class ImageExtensionTest extends TestCase {

  /**
   * The Twig environment.
   *
   * @var \Twig\Environment
   */
  protected Environment $twig;

  /**
   * The ImageExtension instance.
   *
   * @var \Drupal\twig_placeholders\Twig\ImageExtension
   */
  protected ImageExtension $extension;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the Twig environment with autoescape disabled.
    $this->twig = new Environment(new ArrayLoader(), [
      // Disable autoescaping to correctly test raw HTML output.
      'autoescape' => FALSE,
    ]);

    // Create an instance of ImageExtension.
    $this->extension = new ImageExtension();

    // Register the extension in Twig.
    $this->twig->addExtension($this->extension);
  }

  /**
   * Tests that the tp_image function is registered.
   */
  public function testFunctionRegistration(): void {
    $functions = $this->extension->getFunctions();
    $functionNames = array_map(fn(TwigFunction $func) => $func->getName(), $functions);

    $this->assertContains('tp_image', $functionNames, 'The tp_image function is registered.');
  }

  /**
   * Tests the tp_image function with default parameters.
   */
  public function testDefaultImageGeneration(): void {
    $output = $this->extension->generatePlaceholderImage();

    $this->assertIsArray($output, 'Output is a render array.');
    $this->assertArrayHasKey('#theme', $output, 'Render array has #theme key.');
    $this->assertEquals('image', $output['#theme'], 'Render array uses the "image" theme.');
    $this->assertArrayHasKey('#uri', $output, 'Render array has #uri key.');
    $this->assertIsString($output['#uri'], 'The #uri key is a string.');
    $this->assertStringContainsString('https://picsum.photos/', $output['#uri'], 'Image URL is correct.');
  }

  /**
   * Tests tp_image with URL-only mode.
   */
  public function testImageUrlOnly(): void {
    $output = $this->extension->generatePlaceholderImage(TRUE);

    $this->assertIsString($output, 'Output is a string.');
    $this->assertStringContainsString('https://picsum.photos/', $output, 'Correct image URL returned.');
  }

  /**
   * Tests tp_image with custom width and height.
   */
  public function testCustomDimensions(): void {
    $output = $this->extension->generatePlaceholderImage(FALSE, 600, 300);

    $this->assertIsArray($output, 'Output is a render array.');
    $this->assertArrayHasKey('#uri', $output, 'Render array has #uri key.');
    $this->assertIsString($output['#uri'], 'The #uri key is a string.');
    $this->assertStringContainsString('https://picsum.photos/600/300', $output['#uri'], 'Custom dimensions are applied.');
  }

  /**
   * Tests grayscale option.
   */
  public function testGrayscale(): void {
    $output = $this->extension->generatePlaceholderImage(FALSE, 800, 450, NULL, TRUE);

    if (is_array($output) && isset($output['#uri']) && is_string($output['#uri'])) {
      $this->assertStringContainsString('?grayscale', $output['#uri'], 'Grayscale is applied.');
    }
    else {
      $this->fail('Output is not a valid render array with a string #uri key.');
    }
  }

  /**
   * Tests blur effect.
   */
  public function testBlurEffect(): void {
    $output = $this->extension->generatePlaceholderImage(FALSE, 800, 450, NULL, FALSE, 5);

    if (is_array($output) && isset($output['#uri']) && is_string($output['#uri'])) {
      $this->assertStringContainsString('?blur=5', $output['#uri'], 'Blur effect is applied.');
    }
    else {
      $this->fail('Output is not a valid render array with a string #uri key.');
    }
  }

  /**
   * Tests specific image ID.
   */
  public function testSpecificImageId(): void {
    $output = $this->extension->generatePlaceholderImage(FALSE, 800, 450, 10);

    if (is_array($output) && isset($output['#uri']) && is_string($output['#uri'])) {
      $this->assertStringContainsString('/id/10/', $output['#uri'], 'Specific image ID is applied.');
    }
    else {
      $this->fail('Output is not a valid render array with a string #uri key.');
    }
  }

  /**
   * Tests image extension selection.
   */
  public function testImageExtension(): void {
    $output = $this->extension->generatePlaceholderImage(FALSE, 800, 450, NULL, FALSE, NULL, 'webp');

    if (is_array($output) && isset($output['#uri']) && is_string($output['#uri'])) {
      $this->assertStringContainsString('.webp', $output['#uri'], 'WebP format is used.');
    }
    else {
      $this->fail('Output is not a valid render array with a string #uri key.');
    }
  }

}
