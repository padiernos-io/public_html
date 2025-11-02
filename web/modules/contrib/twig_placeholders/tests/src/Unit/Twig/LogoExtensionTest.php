<?php

namespace Drupal\Tests\twig_placeholders\Unit\Twig;

use Drupal\twig_placeholders\Twig\LogoExtension;
use Drupal\Core\Messenger\MessengerInterface;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * @coversDefaultClass \Drupal\twig_placeholders\Twig\LogoExtension
 *
 * @group twig_placeholders
 */
class LogoExtensionTest extends TestCase {

  /**
   * The Twig environment.
   *
   * @var \Twig\Environment
   */
  protected Environment $twig;

  /**
   * The LogoExtension instance.
   *
   * @var \Drupal\twig_placeholders\Twig\LogoExtension
   */
  protected LogoExtension $extension;

  /**
   * The mock messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a mock messenger service.
    $this->messenger = $this->createMock(MessengerInterface::class);

    // Create the Twig environment with autoescape disabled.
    $this->twig = new Environment(new ArrayLoader(), [
      'autoescape' => FALSE,
    ]);

    // Create an instance of LogoExtension with the mock messenger.
    $this->extension = new LogoExtension($this->messenger);

    // Register the extension in Twig.
    $this->twig->addExtension($this->extension);
  }

  /**
   * Tests that the tp_logo function is registered.
   */
  public function testFunctionRegistration(): void {
    $functions = $this->extension->getFunctions();
    $functionNames = array_map(fn(TwigFunction $func) => $func->getName(), $functions);

    $this->assertContains('tp_logo', $functionNames, 'The tp_logo function is registered.');
  }

  /**
   * Tests tp_logo default behavior.
   */
  public function testDefaultLogoSelection(): void {
    $output = $this->extension->selectPlaceholderLogo();

    $this->assertArrayHasKey('#markup', $output, 'Render array has #markup key.');
    $this->assertIsString($output['#markup'], 'The #markup key is a string.');
    $this->assertStringContainsString('<svg', $output['#markup'], 'Output contains SVG markup.');
  }

  /**
   * Tests tp_logo with a specific category.
   */
  public function testCategoryFiltering(): void {
    $output = $this->extension->selectPlaceholderLogo('badge');

    $this->assertArrayHasKey('#markup', $output, 'Render array has #markup key.');
    $this->assertIsString($output['#markup'], 'The #markup key is a string.');
    $this->assertStringContainsString('<svg', $output['#markup'], 'Badge category logo is returned.');
  }

  /**
   * Tests tp_logo with an invalid category.
   */
  public function testInvalidCategoryWarning(): void {
    $messenger_mock = $this->createMock(MessengerInterface::class);
    $messenger_mock->expects($this->once())
      ->method('addWarning')
      ->with($this->stringContains('Invalid SVG category'));
    $extension_with_messenger = new LogoExtension($messenger_mock);
    $output = $extension_with_messenger->selectPlaceholderLogo('invalid_category');
    $this->assertArrayHasKey('#markup', $output, 'Render array has #markup key.');
    $this->assertIsString($output['#markup'], 'The #markup key is a string.');
    $this->assertStringContainsString('<svg', $output['#markup'], 'Fallback logo is returned for invalid category.');
  }

  /**
   * Tests tp_logo with a specific logo ID.
   */
  public function testSpecificLogoSelection(): void {
    $output = $this->extension->selectPlaceholderLogo(2);
    $this->assertArrayHasKey('#markup', $output, 'Render array has #markup key.');
    $this->assertIsString($output['#markup'], 'The #markup key is a string.');
    $this->assertStringContainsString('<svg', $output['#markup'], 'Specific logo ID is returned.');
  }

}
