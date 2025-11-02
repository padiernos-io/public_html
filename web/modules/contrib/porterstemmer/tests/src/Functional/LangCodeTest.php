<?php

namespace Drupal\Tests\porterstemmer\Functional;

use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Tests that EN language search terms are stemmed and return stemmed content.
 *
 * @group porterstemmer
 */
class LangCodeTest extends BrowserTestBase {

  use CronRunTrait;

  /**
   * Default theme.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'search',
    'porterstemmer',
    'language',
    'dblog',
  ];

  /**
   * A user with permission to administer nodes.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testUser;

  /**
   * An array of content for testing purposes.
   *
   * @var string[]
   */
  protected $testData = [
    'First Page' => 'I walk through the streets, looking around for trouble.',
    'Second Page' => 'I walked home from work today.',
    'Third Page' => 'I am always walking everywhere.',
  ];

  /**
   * An array of search terms.
   *
   * @var string[]
   */
  protected $searches = [
    'walk',
    'walked',
    'walking',
  ];

  /**
   * An array of nodes created for testing purposes.
   *
   * @var \Drupal\node\NodeInterface[]
   */
  protected $nodes;

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();

    $this->testUser = $this->drupalCreateUser([
      'search content',
      'access content',
      'administer nodes',
      'access site reports',
      'use advanced search',
      'administer languages',
      'access administration pages',
      'administer site configuration',
    ]);
    $this->drupalLogin($this->testUser);

    // Create Basic page node types.
    if ($this->profile != 'standard') {
      $this
        ->drupalCreateContentType([
          'type' => 'page',
          'name' => 'Basic page',
        ]);
    }

    // Add a new language.
    ConfigurableLanguage::createFromLangcode('fr')->save();

    // Make the body field translatable. The title is already translatable by
    // definition.
    $field_storage = FieldStorageConfig::loadByName('node', 'body');
    $field_storage->setTranslatable(TRUE);
    $field_storage->save();

    // Create EN language nodes.
    foreach ($this->testData as $title => $body) {
      $info = [
        'title' => $title . ' (EN)',
        'body' => [['value' => $body]],
        'type' => 'page',
        'langcode' => 'en',
      ];
      $this->nodes[$title] = $this->drupalCreateNode($info);
    }

    // Create non-EN nodes.
    foreach ($this->testData as $title => $body) {
      $info = [
        'title' => $title . ' (FR)',
        'body' => [['value' => $body]],
        'type' => 'page',
        'langcode' => 'fr',
      ];
      $this->nodes[$title] = $this->drupalCreateNode($info);
    }

    // Create language-unspecified nodes.
    foreach ($this->testData as $title => $body) {
      $info = [
        'title' => $title . ' (UND)',
        'body' => [['value' => $body]],
        'type' => 'page',
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      ];
      $this->nodes[$title] = $this->drupalCreateNode($info);
    }

    // Run cron to ensure the content is indexed.
    $this->cronRun();
    $this->drupalGet('admin/reports/dblog');
    $this->assertSession()->pageTextContains('Cron run completed');
  }

  /**
   * Test that search variations return English language results.
   */
  public function testStemSearching(): void {

    foreach ($this->searches as $search) {
      $this->drupalGet('search/node');
      $this->submitForm(['keys' => $search], 'Search');

      // Verify that all English-language test node variants show up in results.
      foreach ($this->testData as $title => $body) {
        // Search returns English-language node with body.
        $this->assertSession()->pageTextContains($title . ' (EN)');
      }

      // Check for results by language.
      switch ($search) {
        case 'walk':
          // Search does not show stemmed non-English results.
          $this->assertSession()->pageTextNotContains('Second Page (FR)');
          // Search does not show stemmed language-unspecified results.
          $this->assertSession()->pageTextNotContains('Second Page (UND)');
          break;

        case 'walked':
          // Search does not show stemmed non-English results.
          $this->assertSession()->pageTextNotContains('Second Page (FR)');
          // Search doesn't show stemmed language-neutral results.
          $this->assertSession()->pageTextNotContains('Second Page (UND)');
          break;

        case 'walking':
          // Search does show matching non-English results.
          $this->assertSession()->pageTextContains('First Page (FR)');
          // Search does show matching language-unspecified results.
          $this->assertSession()->pageTextContains('First Page (UND)');
          break;

      }
    }
  }

}
