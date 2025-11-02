<?php

declare(strict_types=1);

namespace Drupal\Tests\drupal_cms_seo_tools\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\FunctionalTests\Core\Recipe\RecipeTestTrait;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\BrowserTestBase;

/**
 * @group drupal_cms_seo_tools
 */
class ComponentValidationTest extends BrowserTestBase {

  use RecipeTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node'];

  public function test(): void {
    $dir = realpath(__DIR__ . '/../../..');

    // Create a content type so we can test the changes made by the recipe.
    $node_type = $this->drupalCreateContentType(['type' => 'test'])->id();

    // The recipe should apply cleanly.
    $this->applyRecipe($dir);
    // Apply it again to prove that it is idempotent.
    $this->applyRecipe($dir);

    // There should be an SEO image field on our test content type, referencing
    // image media.
    $field_settings = FieldConfig::loadByName('node', $node_type, 'field_seo_image')?->getSettings();
    $this->assertIsArray($field_settings);
    $this->assertSame('default:media', $field_settings['handler']);
    $this->assertContains('image', $field_settings['handler_settings']['target_bundles']);

    // Check sitemap works as expected for anonymous users.
    $this->checkSitemap();

    // Check sitemap works as expected for authenticated users too.
    $authenticated = $this->createUser();
    $this->drupalLogin($authenticated);
    $this->checkSitemap();
  }

  /**
   * Checks that the sitemap is accessible and contains the expected links.
   */
  private function checkSitemap(): void {
    // Create a main menu link to ensure it shows up in the site map.
    $node = $this->drupalCreateNode(['type' => 'test']);
    $menu_link = MenuLinkContent::create([
      'title' => $node->getTitle(),
      'link' => 'internal:' . $node->toUrl()->toString(),
      'menu_name' => 'main',
    ]);
    $menu_link->save();

    $this->drupalGet('/sitemap');

    $assert_session = $this->assertSession();
    $assert_session->statusCodeEquals(200);
    $assert_session->linkByHrefNotExists('/rss.xml');

    $site_map = $assert_session->elementExists('css', '.sitemap');
    $site_name = $this->config('system.site')->get('name');
    $this->assertTrue($site_map->hasLink("Front page of $site_name"), 'Front page link does not appear in the site map.');
    $this->assertTrue($site_map->hasLink($menu_link->label()), 'Main menu links do not appear in the site map.');
  }

}
