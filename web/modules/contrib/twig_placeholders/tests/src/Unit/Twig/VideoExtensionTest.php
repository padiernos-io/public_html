<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\twig_placeholders\Twig\VideoExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\VideoExtension
 *
 * @group twig_placeholders
 */
class VideoExtensionTest extends TestCase {

  /**
   * The Twig environment used for rendering.
   *
   * @var \Twig\Environment
   */
  protected Environment $twig;

  /**
   * The VideoExtension instance being tested.
   *
   * @var \Drupal\twig_placeholders\Twig\VideoExtension
   */
  protected VideoExtension $extension;

  /**
   * Sets up the test environment before each test.
   */
  protected function setUp(): void {
    parent::setUp();

    $this->twig = new Environment(new ArrayLoader(), [
      'autoescape' => FALSE,
    ]);

    $messenger_mock = $this->createMock(MessengerInterface::class);
    $this->extension = new VideoExtension($messenger_mock);
    $this->twig->addExtension($this->extension);
  }

  /**
   * Tests that the tp_video function is properly registered in Twig.
   */
  public function testFunctionRegistration(): void {
    $functions = $this->extension->getFunctions();
    $functionNames = array_map(fn(TwigFunction $func) => $func->getName(), $functions);
    $this->assertContains('tp_video', $functionNames, 'The tp_video function is registered.');
  }

  /**
   * Tests the default video generation functionality.
   */
  public function testDefaultVideoGeneration(): void {
    $output = $this->extension->generatePlaceholderVideo();

    if (is_array($output)) {
      $this->validateRenderArray($output, '1080', 'mp4');
    }
    else {
      $this->fail('Expected render array, but got: ' . gettype($output));
    }

    $this->validateRenderArray($output, '1080', 'mp4');
  }

  /**
   * Tests custom video size generation.
   */
  public function testCustomVideoSize(): void {
    $output = $this->extension->generatePlaceholderVideo(FALSE, 'Big_Buck_Bunny', '720');

    if (is_array($output)) {
      $this->validateRenderArray($output, '720', 'mp4');
    }
    else {
      $this->fail('Expected render array, but got: ' . gettype($output));
    }
  }

  /**
   * Tests handling of invalid video ID.
   */
  public function testInvalidVideoId(): void {
    $messenger_mock = $this->createMock(MessengerInterface::class);
    $messenger_mock->expects($this->once())
      ->method('addWarning')
      ->with($this->stringContains('Invalid video ID'));
    $extension_with_messenger = new VideoExtension($messenger_mock);
    $output = $extension_with_messenger->generatePlaceholderVideo(FALSE, 'Invalid_ID');

    if (is_array($output)) {
      $this->validateRenderArray($output, '1080', 'mp4');
    }
    else {
      $this->fail('Output is not an array.');
    }
  }

  /**
   * Tests handling of invalid video size.
   */
  public function testInvalidVideoSize(): void {
    $messenger_mock = $this->createMock(MessengerInterface::class);
    $messenger_mock->expects($this->once())
      ->method('addWarning')
      ->with($this->stringContains('Invalid video size'));
    $extension_with_messenger = new VideoExtension($messenger_mock);
    $output = $extension_with_messenger->generatePlaceholderVideo(FALSE, 'Big_Buck_Bunny', '999');

    if (is_array($output)) {
      $this->validateRenderArray($output, '1080', 'mp4');
    }
    else {
      $this->fail('Output is not an array.');
    }
  }

  /**
   * Tests handling of invalid video extension.
   */
  public function testInvalidVideoExtension(): void {
    $messenger_mock = $this->createMock(MessengerInterface::class);
    $messenger_mock->expects($this->once())
      ->method('addWarning')
      ->with($this->stringContains('Invalid video extension'));
    $extension_with_messenger = new VideoExtension($messenger_mock);
    $output = $extension_with_messenger->generatePlaceholderVideo(FALSE, 'Big_Buck_Bunny', '1080', 'avi');

    if (is_array($output)) {
      $this->validateRenderArray($output, '1080', 'mp4');
    }
    else {
      $this->fail('Output is not an array.');
    }
  }

  /**
   * Validates the render array for the placeholder video.
   *
   * @param array<string, mixed> $output
   *   The render array to validate.
   * @param string $expectedSize
   *   The expected video size.
   * @param string $expectedExtension
   *   The expected video extension.
   */
  private function validateRenderArray(array $output, string $expectedSize = '1080', string $expectedExtension = 'mp4'): void {
    $this->assertArrayHasKey('#files', $output, 'Render array has #files key.');
    $this->assertIsArray($output['#files'], 'Files is an array.');
    $this->assertIsArray($output['#files'][0], 'First file in output is an array.');
    $this->assertArrayHasKey('source_attributes', $output['#files'][0], 'Source attributes is present.');
    $this->assertInstanceOf(Attribute::class, $output['#files'][0]['source_attributes'], 'Source attributes is an AttributeString.');
    $source_attributes = (string) $output['#files'][0]['source_attributes']->__toString();
    $this->assertStringContainsString('src=', $source_attributes, 'Src attribute is present.');
    preg_match('/src="([^"]+)"/', $source_attributes, $matches);
    $this->assertNotEmpty($matches, 'Src attribute is found.');
    $src = $matches[1];
    $this->assertStringContainsString($expectedSize, $src, 'Correct video size.');
    $this->assertStringContainsString($expectedExtension, $src, 'Correct video extension.');
  }

}
