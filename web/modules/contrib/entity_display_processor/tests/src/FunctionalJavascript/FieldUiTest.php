<?php

declare(strict_types=1);

namespace Drupal\Tests\entity_display_processor\FunctionalJavascript;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

class FieldUiTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_display_processor_test',
    'node',
    'field_ui',
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

    // This module does not introduce new permissions.
    // So simply create an admin user.
    $webmaster = $this->drupalCreateUser([
      'administer node display',
    ]);
    $this->drupalLogin($webmaster);

    $this->drupalCreateContentType([
      'name' => 'News',
      'type' => 'news',
    ]);
  }

  public function testFieldUi(): void {
    $this->assertSettings([]);

    $this->drupalGet('admin/structure/types/manage/news/display/teaser');
    $get_details_element = fn () => $this->assertSession()->elementExists('css', '[data-drupal-selector="edit-entity-display-processor"]');
    $get_select_element = fn () => $this->assertSession()->selectExists('Entity display processor plugin');

    // The select details element starts closed if no processor selected.
    $this->assertFalse($get_select_element()->isVisible());
    $get_details_element()->pressButton('Entity display processor');
    $this->assertTrue($get_select_element()->isVisible());

    $this->assertSame('', $get_select_element()->getValue());
    $get_select_element()->selectOption('Add custom classes');
    $this->assertSession()->waitForElement('css', '[data-current-id=add_custom_classes]');
    $this->assertSession()->fieldExists('Custom classes')
      ->setValue('abc def');
    $this->submitForm([], 'Save');

    $this->assertSession()->statusMessageContains('Your settings have been saved.');
    $this->assertSame('custom_classes', $get_select_element()->getValue());
    $this->assertTrue($get_select_element()->isVisible());
    $this->assertSettings([
      'processor' => [
        'id' => 'custom_classes',
        'settings' => [
          'classes' => 'abc def',
        ],
      ],
    ]);

    $get_select_element()->selectOption('Wrapper div');
    $this->assertSession()->waitForElement('css', '[data-current-id=wrapper_div]');
    $this->submitForm([], 'Save');

    $this->assertSession()->statusMessageContains('Your settings have been saved.');
    $this->assertSame('wrapper_div', $get_select_element()->getValue());
    $this->assertTrue($get_select_element()->isVisible());
    $this->assertSettings([
      'processor' => [
        'id' => 'wrapper_div',
      ],
    ]);

    $get_select_element()->selectOption('- None -');
    $this->assertSession()->waitForElement('css', '[data-current-id=""]');
    $this->submitForm([], 'Save');

    $this->assertSession()->statusMessageContains('Your settings have been saved.');
    $this->assertSame('', $get_select_element()->getValue());
    $this->assertFalse($get_select_element()->isVisible());
    $this->assertSettings([]);
  }

  /**
   * Asserts the third party settings for this module for the 'news' teaser.
   *
   * @param array $expected
   *   Expected settings.
   */
  protected function assertSettings(array $expected): void {
    $storage = \Drupal::entityTypeManager()->getStorage('entity_view_display');
    assert($storage instanceof ConfigEntityStorage);
    $display = $storage->loadUnchanged('node.news.teaser');
    assert($display instanceof EntityViewDisplayInterface);
    $settings = $display->getThirdPartySettings('entity_display_processor');
    $this->assertSame($expected, $settings);
  }

}
