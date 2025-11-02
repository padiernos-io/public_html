<?php

declare(strict_types=1);

namespace Drupal\Tests\form_decorator\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Tests registration of a user with short name.
 */
class UserRegistrationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'form_decorator_example',
    'form_decorator',
    'field_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the ValidateOnly form decorator example.
   */
  public function testRegistrationWithShortUsername(): void {
    $config = $this->config('user.settings');
    // Don't require email verification and allow registration by site visitors
    // without administrator approval.
    $config
      ->set('verify_mail', FALSE)
      ->set('register', UserInterface::REGISTER_VISITORS)
      ->save();

    $edit = [];
    $edit['name'] = $this->randomMachineName(6);
    $edit['mail'] = $edit['name'] . '@example.com';

    // Use a matching password.
    $edit['pass[pass1]'] = '99999';
    $edit['pass[pass2]'] = '99999';
    $this->drupalGet('user/register');
    $this->submitForm($edit, 'Create new account');
    $this->assertSession()->pageTextContains('Your username should have at least 7 characters.');
  }

}
