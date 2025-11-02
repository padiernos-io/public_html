<?php

namespace Drupal\Tests\entity_body_class\Functional;

use Drupal\Tests\node\Functional\NodeTestBase;

/**
 * Class EntityBodyClassTest. The base class for testing body classes.
 */
class EntityBodyClassTest extends NodeTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['node', 'entity_body_class'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test body classes.
   */
  public function testBodyClasses() {
    // Log in as an admin user with permission to manage body classes.
    $admin = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($admin);

    // Create a new node type.
    $this->createContentType(['type' => 'class']);

    // Check the status of the page.
    $this->drupalGet('node/add/class');
    $this->assertSession()->statusCodeEquals(200);

    // Create a new node with a random name and body class.
    $edit = [];
    $edit['title[0][value]'] = $this->randomMachineName(8);
    $edit['entity_body_class[0][value]'] = $this->randomMachineName(8);
    $this->drupalGet('node/add/class');
    $this->submitForm($edit, 'Save');

    // Get created node.
    $node = $this->drupalGetNodeByTitle($edit['title[0][value]']);

    // Check if the body class exists on the page.
    $this->drupalGet("node/{$node->id()}");
    $this->assertSession()->pageTextContains($edit['title[0][value]']);
    $this->assertSession()->elementExists('css', 'body.' . $edit['entity_body_class[0][value]']);
  }

}
