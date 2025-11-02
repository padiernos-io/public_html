<?php

declare(strict_types=1);

namespace Drupal\Tests\form_decorator\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;
use Drupal\user\Form\UserLoginForm;

/**
 * Tests for form_decorator examples.
 */
class FormDecoratorKernelTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'form_decorator',
    'form_decorator_example',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['system', 'node']);
    $this->container->get('module_handler')->loadInclude('user', 'install');
    user_install();
    $anonymous_user = User::load(0);
    $anonymous_user->set('name', 'anonymous');
    $anonymous_user->save();

    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();

    $user = User::create(['name' => 'Test User']);
    $user->save();
    $this->container->get('current_user')->setAccount($user);
  }

  /**
   * Tests if the new datepicker field exists.
   */
  public function testCreatedDatepickerField(): void {
    $this->container->get('current_user')->setAccount(User::load(1));

    $entity_form_builder = $this->container->get('entity.form_builder');

    $node = $this->container->get('entity_type.manager')->getStorage('node')->create([
      'type' => 'article',
      'title' => 'Test Article',
    ]);

    $form = $entity_form_builder->getForm($node);

    $this->assertArrayHasKey('created_datepicker', $form, 'The created_datepicker field is present in the node form.');
    $this->assertEquals('datetime', $form['created_datepicker']['#type'], 'The created_datepicker field is of type datetime.');
  }

  /**
   * Tests if the info is present on the user login form.
   */
  public function testUserLoginFormInfo(): void {
    $form = \Drupal::formBuilder()->getForm(UserLoginForm::class);

    $this->assertArrayHasKey('info', $form, 'The info field is present in the user login form.');
    $this->assertEquals('If you are not logged in you work as anonymous.', $form['info']['#markup'], 'The created_datepicker field is of type datetime.');
  }

  /**
   * Tests if form decorators are applied in the specified order.
   */
  public function testDecoratorWeight(): void {
    $form = \Drupal::formBuilder()->getForm(UserLoginForm::class);

    $this->assertArrayHasKey('test_string', $form, 'The test_string field is present in the user login form.');
    $this->assertEquals('FooBar', $form['test_string']['#markup'], 'Form decorators are applied in the specified order');
  }

}
