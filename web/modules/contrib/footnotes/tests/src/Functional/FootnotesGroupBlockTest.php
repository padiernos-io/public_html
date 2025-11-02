<?php

namespace Drupal\Tests\footnotes\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\block\Functional\AssertBlockAppearsTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\ckeditor5\Traits\CKEditor5TestTrait;
use Drupal\Tests\footnotes\Traits\FootnotesTestHelperTrait;

/**
 * Test the footnotes group block.
 *
 * @group footnotes
 */
class FootnotesGroupBlockTest extends BrowserTestBase {

  use StringTranslationTrait;
  use CKEditor5TestTrait;
  use FootnotesTestHelperTrait;
  use AssertBlockAppearsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'ckeditor5',
    'filter',
    'ckeditor5_test',
    'footnotes',
    'block',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->setUpFormatAndEditor();

    $this->updateEditorSettings([
      'footnotes_collapse' => FALSE,
      'footnotes_css' => FALSE,
      'footnotes_footer_disable' => TRUE,
    ]);

    $this->setUpNodeTypeAndField();
    $this->setUpDefaultUserWithAccess();
  }

  /**
   * Tests CKEditor5 plugin dialog loads.
   */
  public function testGroupBlock() {

    // Build some default footnotes.
    $note1_build = $this->buildFootnote('Note <strong>one</strong> test');
    $note1 = $this->renderFootnote($note1_build);
    $note2_build = $this->buildFootnote('Note <strong>two</strong> test');
    $note2 = $this->renderFootnote($note2_build);
    $body = '<p>testFootnotesFooterDisable' . $this->randomMachineName(100) . $note1 . '</p>';
    $body .= '<p>' . $this->randomMachineName(100) . $note2 . '</p>';

    // Add the footnotes group block.
    $settings = [
      'theme' => 'stark',
      'region' => 'footer',
      'label' => 'Footnotes Group',
      'context_mapping' => [
        'entity' => '@node.node_route_context:node',
      ],
      'group_via_js' => FALSE,
    ];

    $this->drupalPlaceBlock('footnotes_group', $settings);

    // Create a node.
    $node = $this->drupalCreateNode([
      'title' => $this->randomString(),
      'type' => 'page',
      'body' => [
        'value' => $body,
        'format' => 'filtered_html',
      ],
    ]);

    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementExists('css', '#footnotes_group');
    $this->assertSession()->elementTextContains('css', '#footnotes_group li:first-child', 'Note one test');
    $this->assertSession()->elementTextContains('css', '#footnotes_group li:last-child', 'Note two test');

    // Make another request to ensure the block is visible with caching.
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->elementExists('css', '#footnotes_group');
    $this->assertSession()->elementTextContains('css', '#footnotes_group li:first-child', 'Note one test');
    $this->assertSession()->elementTextContains('css', '#footnotes_group li:last-child', 'Note two test');

    // Assert X-Drupal-Dynamic-Cache reports as HIT.
    $this->assertSession()->responseHeaderEquals('X-Drupal-Dynamic-Cache', 'HIT');
  }

  /**
   * Tests collapsing and not collapsing identical footnote texts.
   */
  public function testCollapseFootnotesGroupBlock() {
    // Build two identical footnotes, adding some items before and after to
    // ensure those numbers are unaffected.
    $note_build = $this->buildFootnote('First non-identical note');
    $note_before = $this->renderFootnote($note_build);
    $note_build = $this->buildFootnote('Identical note test');
    $note = $this->renderFootnote($note_build);
    $note_build = $this->buildFootnote('Third non-identical note');
    $note_after = $this->renderFootnote($note_build);
    $body = '<p>' . $this->randomMachineName(100) . $note_before . '</p>';
    $body .= '<p>' . $this->randomMachineName(100) . $note . '</p>';
    $body .= '<p>' . $this->randomMachineName(100) . $note . '</p>';
    $body .= '<p>' . $this->randomMachineName(100) . $note_after . '</p>';

    // Place the footnotes group block.
    $settings = [
      'theme' => 'stark',
      'region' => 'footer',
      'label' => 'Footnotes Group',
      'context_mapping' => [
        'entity' => '@node.node_route_context:node',
      ],
      'group_via_js' => FALSE,
    ];
    $this->drupalPlaceBlock('footnotes_group', $settings);

    // Test with collapse disabled.
    $this->updateEditorSettings([
      'footnotes_collapse' => FALSE,
      'footnotes_css' => FALSE,
      'footnotes_footer_disable' => TRUE,
    ]);
    $node = $this->drupalCreateNode([
      'title' => $this->randomString(),
      'type' => 'page',
      'body' => [
        'value' => $body,
        'format' => 'filtered_html',
      ],
    ]);
    $this->drupalGet('node/' . $node->id());
    // Should see two separate list items for the identical notes.
    $this->assertSession()->elementsCount('css', '#footnotes_group li', 4);

    // Check citations matching expectations.
    $elements = $this->getSession()->getPage()->findAll('css', 'article div .footnote__citation');
    $texts = [];
    foreach ($elements as $element) {
      $texts[] = $element->getText();
    }
    $this->assertSame([
      '1',
      '2',
      '3',
      '4',
    ], $texts);

    // Check footnote references matching expectations.
    $elements = $this->getSession()->getPage()->findAll('css', '#footnotes_group li a');
    $texts = [];
    foreach ($elements as $element) {
      $texts[] = $element->getText();
    }
    $this->assertSame([
      '1',
      '2',
      '3',
      '4',
    ], $texts);

    // Test with collapse enabled.
    $this->updateEditorSettings([
      'footnotes_collapse' => TRUE,
      'footnotes_css' => FALSE,
      'footnotes_footer_disable' => TRUE,
    ]);
    // Resave node to clear cache and apply new settings.
    $node->setTitle('Resave for collapse');
    $node->save();
    $this->drupalGet('node/' . $node->id());
    // Should see only one list item for the collapsed identical notes.
    $this->assertSession()->elementsCount('css', '#footnotes_group li', 3);

    // Check citations matching expectations.
    $elements = $this->getSession()->getPage()->findAll('css', 'article div .footnote__citation');
    $texts = [];
    foreach ($elements as $element) {
      $texts[] = $element->getText();
    }
    $this->assertSame([
      '1',
      '2',
      '2',
      '3',
    ], $texts);

    // Check footnote references matching expectations.
    $elements = $this->getSession()->getPage()->findAll('css', '#footnotes_group li a');
    $texts = [];
    foreach ($elements as $element) {
      $texts[] = $element->getText();
    }
    $this->assertSame([
      '1',
      '2a',
      '2b',
      '3',
    ], $texts);
  }

}
