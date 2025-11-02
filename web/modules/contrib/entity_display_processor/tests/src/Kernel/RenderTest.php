<?php

declare(strict_types=1);

namespace Drupal\Tests\entity_display_processor\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\NodeCreationTrait;

class RenderTest extends EntityKernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'user',
    'system',
    'entity_display_processor',
    'entity_display_processor_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->installConfig(['system', 'node']);
    NodeType::create([
      'type' => 'news',
      'name' => 'News',
    ])->save();
    \Drupal::service('theme_installer')->install(['stark']);
    $this->config('system.theme')->set('default', 'stark')->save();
    EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'news',
      'mode' => 'teaser',
      'status' => TRUE,
    ])->save();
  }

  public function testRendering(): void {
    $node = Node::create([
      'title' => 'Test news',
      'type' => 'news',
    ]);
    $node->save();
    $output = $this->renderNodeTeaser($node);
    $this->assertSame(1, preg_match(
      '@^<article>(.*Test news.*)</article>$@s',
      trim($output),
      $matches,
    ), 'Unexpected output: ' . var_export(trim($output), TRUE));

    $this->setProcessor('custom_classes', ['classes' => 'abc def']);
    $output = $this->renderNodeTeaser($node);
    $this->assertSame(
      '<article class="abc def">' . $matches[1] . '</article>',
      trim($output),
    );

    $this->setProcessor('wrapper_div');
    $output = $this->renderNodeTeaser($node);
    $this->assertSame(1, preg_match(
      '@^\s*<div class="wrapper">\s*' . preg_quote($matches[0], '@') . '\s*</div>\s*$@',
      $output,
    ), 'Unexpected output: ' . var_export(trim($output), TRUE));

    $this->setProcessor('wrapper_div_alt');
    $output = $this->renderNodeTeaser($node);
    $this->assertSame(1, preg_match(
      '@^\s*<div class="alternative-wrapper">\s*' . preg_quote($matches[0], '@') . '\s*</div>\s*$@',
      $output,
    ), 'Unexpected output: ' . var_export(trim($output), TRUE));
  }

  /**
   * Renders a node in teaser view mode.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to render.
   *
   * @return string
   *   Rendered markup.
   */
  protected function renderNodeTeaser(NodeInterface $node): string {
    $element = \Drupal::entityTypeManager()->getViewBuilder('node')
      ->view($node, 'teaser');
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    $markup = $renderer->renderInIsolation($element);
    return (string) $markup;
  }

  /**
   * Sets the processor plugin for news teaser display.
   *
   * @param string $id
   *   Processor plugin id.
   * @param array $settings
   *   Processor plugin settings.
   */
  protected function setProcessor(string $id, array $settings = []): void {
    $display = EntityViewDisplay::load('node.news.teaser');
    $display->setThirdPartySetting('entity_display_processor', 'processor', [
      'id' => $id,
      'settings' => $settings,
    ]);
    $display->save();
  }

}
