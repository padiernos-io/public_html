<?php

namespace Drupal\Tests\ept_tabs\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\paragraphs\FunctionalJavascript\ParagraphsTestBaseTrait;

/**
 * Tests module installation.
 *
 * @group ept_core
 * @group ept_tabs
 */
class InstallTest extends BrowserTestBase {

  use ContentTypeCreationTrait;
  use ParagraphsTestBaseTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'options',
    'block_field',
    'jquery_ui_tabs',
    'paragraphs',
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

    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Page',
    ]);

    // $this->addParagraphsType('ept_tab');
    $this->assertFalse($this->moduleHandler->moduleExists('ept_tabs'));
    $this->assertFalse($this->moduleHandler->moduleExists('ept_core'));
    $this->assertFalse($this->moduleHandler->moduleExists('views'));
    $this->assertFalse($this->moduleHandler->moduleExists('views_ui'));
    $this->assertFalse($this->moduleHandler->moduleExists('viewsreference'));
    $this->assertTrue($this->moduleInstaller->install(['ept_core']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_core');
    $this->assertTrue($this->moduleInstaller->install(['views']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'views');
    $this->assertTrue($this->moduleInstaller->install(['views_ui']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'views_ui');
    $this->assertTrue($this->moduleInstaller->install(['viewsreference']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'viewsreference');
    $this->assertTrue($this->moduleInstaller->install(['ept_tabs']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_tabs');
    $this->reloadServices();
    $this->assertTrue($this->moduleHandler->moduleExists('ept_tabs'));

    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('Welcome!');
  }

}
