<?php

namespace Drupal\Tests\themespace\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\themespace\Namespaces;

/**
 * Test the themespace provided Namespaces to ensure it's enumerating correctly.
 *
 * The \Drupal\themespace\Namespaces accepts both theme and module namespaces
 * as either \Traversable or PHP arrays. Ensure that both are continuing to
 * enumerate the namespaces correctly.
 *
 * @group themespace
 */
class NamespaceTest extends UnitTestCase {

  /**
   * Example module namespaces to use for testing.
   *
   * @var array<string,string>
   */
  public array $moduleNamespaces = [
    'Drupal\admin_toolbar' => 'modules/contrib/admin_toolbar/src',
    'Drupal\admin_toolbar_tools' => 'modules/contrib/admin_toolbar/admin_toolbar_tools/src',
    'Drupal\announcements_feed' => 'core/modules/announcements_feed/src',
    'Drupal\automated_cron' => 'core/modules/automated_cron/src',
    'Drupal\big_pipe' => 'core/modules/big_pipe/src',
    'Drupal\block' => 'core/modules/block/src',
    'Drupal\block_content' => 'core/modules/block_content/src',
    'Drupal\breakpoint' => 'core/modules/breakpoint/src',
    'Drupal\ckeditor5' => 'core/modules/ckeditor5/src',
    'Drupal\comment' => 'core/modules/comment/src',
    'Drupal\config' => 'core/modules/config/src',
    'Drupal\contact' => 'core/modules/contact/src',
    'Drupal\contextual' => 'core/modules/contextual/src',
    'Drupal\datetime' => 'core/modules/datetime/src',
    'Drupal\dblog' => 'core/modules/dblog/src',
    'Drupal\devel' => 'modules/contrib/devel/src',
    'Drupal\dynamic_page_cache' => 'core/modules/dynamic_page_cache/src',
    'Drupal\editor' => 'core/modules/editor/src',
    'Drupal\field' => 'core/modules/field/src',
    'Drupal\field_ui' => 'core/modules/field_ui/src',
    'Drupal\file' => 'core/modules/file/src',
    'Drupal\filter' => 'core/modules/filter/src',
    'Drupal\help' => 'core/modules/help/src',
    'Drupal\history' => 'core/modules/history/src',
    'Drupal\image' => 'core/modules/image/src',
    'Drupal\inline_form_errors' => 'core/modules/inline_form_errors/src',
    'Drupal\language' => 'core/modules/language/src',
    'Drupal\link' => 'core/modules/link/src',
    'Drupal\media' => 'core/modules/media/src',
    'Drupal\media_library' => 'core/modules/media_library/src',
    'Drupal\menu_link_content' => 'core/modules/menu_link_content/src',
    'Drupal\menu_ui' => 'core/modules/menu_ui/src',
    'Drupal\mysql' => 'core/modules/mysql/src',
    'Drupal\node' => 'core/modules/node/src',
    'Drupal\options' => 'core/modules/options/src',
    'Drupal\page_cache' => 'core/modules/page_cache/src',
    'Drupal\path' => 'core/modules/path/src',
    'Drupal\path_alias' => 'core/modules/path_alias/src',
    'Drupal\responsive_image' => 'core/modules/responsive_image/src',
    'Drupal\system' => 'core/modules/system/src',
    'Drupal\taxonomy' => 'core/modules/taxonomy/src',
    'Drupal\text' => 'core/modules/text/src',
    'Drupal\token' => 'modules/contrib/token/src',
  ];

  /**
   * Example theme namespaces to use for testing.
   *
   * @var array<string,string>
   */
  public array $themespaces = [
    'Drupal\olivero' => 'core/themes/olivero/src',
    'Drupal\claro' => 'core/themes/claro/src',
  ];

  /**
   * Ensure that namespaces values as array produce the expected enumerations.
   */
  public function testArrayArguments(): void {
    $namespaces = new Namespaces($this->moduleNamespaces, $this->themespaces);

    $this->assertEquals($this->moduleNamespaces, iterator_to_array($namespaces->getModuleIterator()));
    $this->assertEquals($this->themespaces, iterator_to_array($namespaces->getThemeIterator()));

    $this->assertEquals(
      $this->moduleNamespaces,
      iterator_to_array($namespaces->getIterator('module')),
      'Failed to match theme iterator namespaces created from array values.',
    );

    $this->assertEquals(
      $this->themespaces,
      iterator_to_array($namespaces->getIterator('theme')),
      'Failed to match theme iterator namespaces created from array values.',
    );

    $this->assertEquals(
      [...$this->moduleNamespaces, ...$this->themespaces],
      iterator_to_array($namespaces->getIterator()),
      'Failed to match all namespaces created from array values.',
    );
  }

  /**
   * Ensure that namespaces arguments using \Traversable iterator correctly.
   */
  public function testTraversableArguments(): void {
    $moduleNamespaces = new \ArrayObject($this->moduleNamespaces);
    $themespaces = new \ArrayObject($this->themespaces);
    $namespaces = new Namespaces($moduleNamespaces, $themespaces);

    $this->assertEquals($this->moduleNamespaces, iterator_to_array($namespaces->getModuleIterator()));
    $this->assertEquals($this->themespaces, iterator_to_array($namespaces->getThemeIterator()));

    $this->assertEquals(
      $this->moduleNamespaces,
      iterator_to_array($namespaces->getIterator('module')),
      'Failed to match theme iterator namespaces created from \Traversables.',
    );

    $this->assertEquals(
      $this->themespaces,
      iterator_to_array($namespaces->getIterator('theme')),
      'Failed to match theme iterator namespaces created from \Traversables.',
    );

    $this->assertEquals(
      [...$this->moduleNamespaces, ...$this->themespaces],
      iterator_to_array($namespaces->getIterator()),
      'Failed to match all namespaces created from \Traversables.',
    );
  }

  /**
   * Ensure that namespaces arguments when mixing arrays and traversables.
   */
  public function testMixedArguments(): void {
    $moduleNamespaces = new \ArrayObject($this->moduleNamespaces);
    $namespaces = new Namespaces($moduleNamespaces, $this->themespaces);

    // Only have to test the value mixing as the other test cover each value
    // type being used separately.
    $this->assertEquals(
      [...$this->moduleNamespaces, ...$this->themespaces],
      iterator_to_array($namespaces->getIterator()),
      'Failed to match all namespaces created from mixing traversables and arrays.',
    );
  }

}
