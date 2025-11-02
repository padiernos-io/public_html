<?php

namespace Drupal\Tests\ept_hero\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests module installation.
 *
 * @group ept_core
 * @group ept_hero
 */
class InstallTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Module handler to ensure installed modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  public $moduleHandler;

  /**
   * Module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  public $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');

    // Set the front page to "/node".
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node')
      ->save(TRUE);
  }

  /**
   * Reloads services used by this test.
   */
  protected function reloadServices() {
    $this->rebuildContainer();
    $this->moduleHandler = $this->container->get('module_handler');
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Tests that the module is installable.
   */
  public function testInstallation() {
    $account = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($account);

    $this->assertFalse($this->moduleHandler->moduleExists('ept_hero'));
    $this->assertFalse($this->moduleHandler->moduleExists('ept_core'));
    $this->assertTrue($this->moduleInstaller->install(['ept_core']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_core');
    $this->assertTrue($this->moduleInstaller->install(['ept_hero']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_hero');
    $this->reloadServices();
    $this->assertTrue($this->moduleHandler->moduleExists('ept_hero'));

    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('Welcome!');
  }

}
