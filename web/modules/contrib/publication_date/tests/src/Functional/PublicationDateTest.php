<?php

namespace Drupal\publication_date\Tests;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\BrowserTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests for publication_date.
 *
 * @group publication_date
 */
class PublicationDateTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
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
   * The user to use for testing.
   *
   * @var \Drupal\user\Entity\User|false
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() : void {
    parent::setUp();

    NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    $this->user = $this->drupalCreateUser([
      'create page content',
      'edit own page content',
      'administer nodes',
      'set page published on date',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Test automatic saving of variables.
   */
  public function testActionSaving() {
    $requestTime = \Drupal::time()->getRequestTime();

    // Create node to edit.
    $node = $this->drupalCreateNode(['status' => 0]);
    $unpublished_node = Node::load($node->id());
    $this->assertNull($unpublished_node->published_at->value);
    $this->assertEquals($requestTime, $unpublished_node->published_at->published_at_or_now, 'Published at or now date is REQUEST_TIME');

    // Publish the node.
    $unpublished_node->setPublished();
    $unpublished_node->save();
    $published_node = Node::load($node->id());
    $this->assertIsNumeric($published_node->published_at->value,
      'Published date is integer/numeric once published');
    $this->assertEquals($requestTime, $published_node->published_at->value,
      'Published date is REQUEST_TIME');
    $this->assertEquals($published_node->published_at->value, $unpublished_node->published_at->published_at_or_now,
      'Published at or now date equals published date');

    // Remember time.
    $time = $published_node->published_at->value;

    // Unpublish the node and check that the field value is maintained.
    $published_node->setPublished();
    $published_node->save();
    $unpublished_node = Node::load($node->id());
    $this->assertEquals($time, $unpublished_node->published_at->value,
      'Published date is maintained when unpublished');

    // Set the field to zero and make sure the published date is empty.
    $unpublished_node->published_at->value = 0;
    $unpublished_node->save();
    $unpublished_node = Node::load($node->id());
    $this->assertEmpty($unpublished_node->published_at->value);

    // Set a custom time and make sure that it is saved.
    $time = $unpublished_node->published_at->value = 122630400;
    $unpublished_node->save();
    $unpublished_node = Node::load($node->id());
    $this->assertEquals($time, $unpublished_node->published_at->value,
      'Custom published date is saved');
    $this->assertEquals($time, $unpublished_node->published_at->published_at_or_now,
      'Published at or now date equals published date');

    // Republish the node and check that the field value is maintained.
    $unpublished_node->setPublished();
    $unpublished_node->save();
    $published_node = Node::load($node->id());
    $this->assertEquals($time, $published_node->published_at->value,
      'Custom published date is maintained when republished');

    // Set the field to zero and make sure the published date is reset.
    $published_node->published_at->value = NULL;
    $published_node->save();
    $published_node = Node::load($node->id());
    $this->assertGreaterThan($time, $published_node->published_at->value, 'Published date is reset');

    // Now try it by purely pushing the forms around.
  }

  /**
   * Test automatic saving of variables via forms.
   */
  public function testActionSavingOnForms() {
    $edit = [];
    $edit["title[0][value]"] = 'publication test node ' . $this->randomMachineName(10);
    $edit['status[value]'] = 1;

    // Hard to test created time == REQUEST_TIME because simpletest launches a
    // new HTTP session, so just check it's set.
    $this->drupalGet('node/add/page');
    $this->submitForm($edit, (string) $this->t('Save'));
    $node = $this->drupalGetNodeByTitle($edit["title[0][value]"]);
    $this->drupalGet('node/' . $node->id() . '/edit');
    $value = $this->getPubDateFieldValue();
    [$date, $time] = explode(' ', $value);

    // Make sure it was created with a Published At set.
    $this->assertNotNull($value, $this->t('Publication date set initially'));

    // Unpublish the node and check that the field value is maintained.
    $edit['status[value]'] = 0;
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->fieldValueEquals('published_at[0][value][date]', $date);
    $this->assertSession()->fieldValueEquals('published_at[0][value][time]', $time);

    // Republish the node and check that the field value is maintained.
    $edit['status[value]'] = 1;
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->fieldValueEquals('published_at[0][value][date]', $date);
    $this->assertSession()->fieldValueEquals('published_at[0][value][time]', $time);

    // Set a custom time and make sure that it is stored correctly.
    $ctime = \Drupal::time()->getRequestTime() - 180;
    $edit['published_at[0][value][date]'] = \Drupal::service('date.formatter')->format($ctime, 'custom', 'Y-m-d');
    $edit['published_at[0][value][time]'] = \Drupal::service('date.formatter')->format($ctime, 'custom', 'H:i:s');
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $value = $this->getPubDateFieldValue();
    [$date, $time] = explode(' ', $value);
    $this->assertEquals(\Drupal::service('date.formatter')->format($ctime, 'custom', 'Y-m-d'), $date, $this->t('Custom date was set'));
    $this->assertEquals(\Drupal::service('date.formatter')->format($ctime, 'custom', 'H:i:s'), $time, $this->t('Custom time was set'));

    // Set the field to empty and make sure the published date is reset.
    $edit['published_at[0][value][date]'] = '';
    $edit['published_at[0][value][time]'] = '';
    sleep(2);
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $new_value = $this->getPubDateFieldValue();
    [, $new_time] = explode(' ', $this->getPubDateFieldValue());
    $this->assertNotNull($new_value, $this->t('Published time was set automatically when there was no value entered'));
    $this->assertNotEquals($new_time, $time, $this->t('The new published-at time is different from the custom time'));
    $this->assertGreaterThan(strtotime($value), strtotime($this->getPubDateFieldValue()), $this->t('The new published-at time is greater than the original one'));

    // Unpublish the node.
    $edit['status[value]'] = 0;
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));

    // Set the field to empty and make sure that it stays empty.
    $edit['published_at[0][value][date]'] = '';
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->submitForm($edit, (string) $this->t('Save'));
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->assertSession()->fieldValueEquals('published_at[0][value][date]', '');
  }

  /**
   * Test that it cares about setting the published_at field.
   */

  /**
   * This is useful for people using 'migrate' etc.
   */
  public function testActionSavingSetDate() {
    $node = $this->drupalCreateNode(['status' => 0]);
    $unpublished_node = Node::load($node->id());
    $this->assertNull($unpublished_node->published_at->value);

    // Now publish this with our custom time...
    $unpublished_node->setPublished();
    $static_time = 12345678;
    $unpublished_node->published_at->value = $static_time;
    $unpublished_node->save();
    $published_node = Node::load($node->id());
    // ...and see if it comes back with it correctly.
    $this->assertIsNumeric($published_node->published_at->value,
      'Published date is integer/numeric once published');
    $this->assertEquals($static_time, $published_node->published_at->value,
      'Published date is set to what we expected');
  }

  /**
   * Returns the value of our published-at field.
   *
   * @return string
   *   Return date and time as string.
   */
  private function getPubDateFieldValue(): string {
    $this->assertSession()->fieldExists('published_at[0][value][date]');
    $field = $this->xpath('//input[@name="published_at[0][value][date]"]');
    $date = (string) $field[0]->getValue();

    $this->assertSession()->fieldExists('published_at[0][value][time]');
    $field = $this->xpath('//input[@name="published_at[0][value][time]"]');
    $time = (string) $field[0]->getValue();
    return trim($date . ' ' . $time);
  }

}
