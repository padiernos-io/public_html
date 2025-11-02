<?php

namespace Drupal\Tests\publication_date\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\NodeType;

/**
 * Tests publication date permissions.
 *
 * @group publication_date
 */
class PublicationDatePermissionsTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'user',
    'publication_date',
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

    // Create a content type for testing.
    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();
  }

  /**
   * Test users without perms can't access the publication date field.
   */
  public function testNoPermissions() {
    // User has node perms, no publication date perms.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
    ]);
    $this->drupalLogin($user);

    // Go to the node creation form.
    $this->drupalGet('node/add/article');

    // The publication date field should not be accessible.
    $this->assertSession()->fieldNotExists('published_at[0][value][date]');
    $this->assertSession()->fieldNotExists('published_at[0][value][time]');
  }

  /**
   * Tests that users with bundle-specific edit permissions can edit the field.
   */
  public function testBundleEditPermissions() {
    // Create a user with bundle-specific publication date edit permissions.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'set article published on date',
    ]);
    $this->drupalLogin($user);

    // Go to the node creation form.
    $this->drupalGet('node/add/article');

    // The publication date field should be accessible and editable.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][date]"]', 'disabled');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][time]"]', 'disabled');
  }

  /**
   * Tests bundle-specific view permissions can only view the field.
   */
  public function testBundleViewPermissions() {
    // Create a user with bundle-specific publication date view permissions.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'view article published on date',
    ]);
    $this->drupalLogin($user);

    // Create a node owned by this user.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test Article',
      'status' => 1,
      'uid' => $user->id(),
    ]);

    // Go to the node edit form.
    $this->drupalGet("node/{$node->id()}/edit");

    // The publication date field should be visible but disabled.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->fieldDisabled('published_at[0][value][date]');
    $this->assertSession()->fieldDisabled('published_at[0][value][time]');
  }

  /**
   * Tests that users with global edit permissions can edit any content type.
   */
  public function testGlobalEditPermissions() {
    // Create a user with global publication date edit permissions.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'set any published on date',
    ]);
    $this->drupalLogin($user);

    // Go to the node creation form.
    $this->drupalGet('node/add/article');

    // The publication date field should be accessible and editable.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][date]"]', 'disabled');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][time]"]', 'disabled');
  }

  /**
   * Tests that users with global view permissions can view any content type.
   */
  public function testGlobalViewPermissions() {
    // Create a user with global publication date view permissions.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'view any published on date',
    ]);
    $this->drupalLogin($user);

    // Create a node owned by this user.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test Article',
      'status' => 1,
      'uid' => $user->id(),
    ]);

    // Go to the node edit form.
    $this->drupalGet("node/{$node->id()}/edit");

    // The publication date field should be visible but disabled.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->fieldDisabled('published_at[0][value][date]');
    $this->assertSession()->fieldDisabled('published_at[0][value][time]');
  }

  /**
   * Tests that users with admin permissions can edit the field.
   */
  public function testAdminPermissions() {
    // Create a user with admin publication date permissions.
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'administer publication date',
    ]);
    $this->drupalLogin($user);

    // Go to the node creation form.
    $this->drupalGet('node/add/article');

    // The publication date field should be accessible and editable.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][date]"]', 'disabled');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][time]"]', 'disabled');
  }

  /**
   * Tests edit permissions should also grant view access.
   */
  public function testPermissionHierarchy() {
    // Create a user with only edit permissions (should also get view access).
    $user = $this->drupalCreateUser([
      'create article content',
      'edit own article content',
      'set article published on date',
    ]);
    $this->drupalLogin($user);

    // Create a node owned by this user.
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => 'Test Article',
      'status' => 1,
      'uid' => $user->id(),
    ]);

    // Go to the node edit form.
    $this->drupalGet("node/{$node->id()}/edit");

    // The publication date field should be accessible and editable.
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][date]"]', 'disabled');
    $this->assertSession()->elementAttributeNotExists('css', 'input[name="published_at[0][value][time]"]', 'disabled');
  }

}
