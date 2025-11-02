<?php

namespace Drupal\Tests\ept_slideshow\Functional;

use Drupal\field\Entity\FieldConfig;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Functional\MediaFunctionalTestTrait;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests module installation.
 *
 * @group ept_core
 * @group ept_slideshow
 */
class InstallTest extends BrowserTestBase {

  use MediaFunctionalTestTrait;
  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'node',
    'link',
    'paragraphs',
    'field_ui',
    'views_ui',
    'media',
    'media_test_source',
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

    $media_type = $this->createMediaType('image', ['id' => 'image']);
    $media_type_id = $media_type->id();
    $media_type->setFieldMap(['name' => 'name']);
    $media_type->save();

    /** @var \Drupal\field\FieldConfigInterface $field */
    // Disable the alt text field, because this is not a JavaScript test and
    // the alt text field will therefore not appear without a full page refresh.
    $field = FieldConfig::load("media.$media_type_id.field_media_image");
    $settings = $field->getSettings();
    $settings['alt_field'] = TRUE;
    $settings['alt_field_required'] = FALSE;
    $field->set('settings', $settings);
    $field->save();

    $this->assertFalse($this->moduleHandler->moduleExists('ept_slideshow'));
    $this->assertFalse($this->moduleHandler->moduleExists('ept_core'));
    $this->assertTrue($this->moduleInstaller->install(['ept_core']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_core');
    $this->assertTrue($this->moduleInstaller->install(['ept_slideshow']));
    \Drupal::service('config.installer')->installDefaultConfig('module', 'ept_slideshow');
    $this->reloadServices();
    $this->assertTrue($this->moduleHandler->moduleExists('ept_slideshow'));

    // Load the front page.
    $this->drupalGet('<front>');

    // Confirm that the site didn't throw a server error or something else.
    $this->assertSession()->statusCodeEquals(200);

    // Confirm that the front page contains the standard text.
    $this->assertSession()->pageTextContains('Welcome!');
  }

}
