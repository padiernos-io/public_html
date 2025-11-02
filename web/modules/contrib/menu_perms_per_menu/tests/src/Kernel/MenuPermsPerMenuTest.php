<?php

namespace Drupal\Tests\menu_perms_per_menu\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test permissions for module menu_perms_per_menu.
 *
 * @group menu_perms_per_menu
 */
class MenuPermsPerMenuTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * HTTP kernel service.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Test user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $siteManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'link',
    'menu_admin_per_menu',
    'menu_link_content',
    'menu_perms_per_menu',
    'menu_ui',
    'system',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->httpKernel = $this->container->get('http_kernel');
    $this->installEntitySchema('menu_link_content');

    // Create 3 menus: footer, main, tools.
    $menus = [
      'footer' => 'Footer',
      'main' => 'Main',
      'tools' => 'Tools',
    ];
    foreach ($menus as $id => $label) {
      Menu::create(['id' => $id, 'label' => $label])->save();
    }

    // Grant our test user:
    // - full access to the footer menu
    // - restricted access to the main menu
    // - no access to the tools menu.
    $this->siteManager = $this->setUpCurrentUser([], [
      // Footer menu.
      'administer footer menu items',
      'add new links to footer menu from menu interface',
      'delete links in footer menu from menu interface',
      'enable/disable links in footer menu',
      'expand links in footer menu',
      'edit link of menu links in footer menu',
      // Main menu.
      'administer main menu items',
    ]);

  }

  /**
   * Site Manager should see only Footer and Main menu at admin/structure/menu.
   */
  public function testMenuList() {
    $request = Request::create('/admin/structure/menu');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->setRawContent($response->getContent());
    $this->assertText('Footer');
    $this->assertText('Main');
    $this->assertNoText('Tools');
  }

  /**
   * Site Manager cannot add a new link in the empty main menu.
   *
   * Testing permission 'add new links to main menu from menu interface' which
   * site manager does not have.
   */
  public function testUserDoesNotSeeAddLinkInEmptyMainMenu() {
    $request = Request::create('/admin/structure/menu/manage/main');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->setRawContent($response->getContent());
    $this->assertText('There are no menu links yet.');
    $this->assertNoLink('Add link');
  }

  /**
   * Site Manager should have a restricted access to the main menu.
   */
  public function testMainMenu() {
    // Add dummy menu item.
    $link = MenuLinkContent::create([
      'title' => 'Test menu item',
      'link' => ['uri' => 'route:<nolink>'],
      'menu_name' => 'main',
    ]);
    $link->save();

    // Disallowed to create menu items from
    // /admin/structure/menu/manage/main/add.
    $request = Request::create('/admin/structure/menu/manage/main/add');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

    // Disallowed to create menu items from admin/structure/menu.
    $request = Request::create('/admin/structure/menu');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    // See https://www.drupal.org/project/drupal/issues/3129006
    $this->assertNoLinkByHref('admin/structure/menu/manage/main/add', 'User disallowed to create menu items from admin/structure/menu');

    // Allowed to edit menu items.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Disallowed to delete menu items from admin/structure/menu/item/%/edit.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertNoLink('Delete');

    // Disallowed to delete menu items from admin/structure/menu/manage/main.
    $request = Request::create('/admin/structure/menu/manage/main');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertNoLink('Delete');

    // Disallowed to delete menu items by accessing
    // /admin/structure/menu/item/%/delete directly.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/delete');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());

    // Disallowed to add child menu items from admin/structure/menu/manage/main.
    $request = Request::create('/admin/structure/menu/manage/main');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertNoLink('Add child');

    // Disallowed to edit the link of a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldDisabled('link[0][uri]');

    // Disallowed to enable/disable a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldDisabled('enabled[value]');

    // Disallowed to expand a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldDisabled('expanded[value]');
  }

  /**
   * Site Manager should have full access to the footer menu.
   */
  public function testFooterMenu() {

    // Site manager sees the "Add link" link in the empty footer menu.
    $request = Request::create('/admin/structure/menu/manage/footer');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    $this->setRawContent($response->getContent());
    $this->assertText('There are no menu links yet.');
    $this->assertLink('Add link');

    // Add dummy menu item.
    $link = MenuLinkContent::create([
      'title' => 'Test menu item',
      'link' => ['uri' => 'route:<nolink>'],
      'menu_name' => 'footer',
    ]);
    $link->save();

    // Allowed to create menu items from admin/structure/menu/manage/footer/add.
    $request = Request::create('/admin/structure/menu/manage/footer/add');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Allowed to create menu items from admin/structure/menu.
    $request = Request::create('/admin/structure/menu');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertLinkByHref('admin/structure/menu/manage/footer/add');

    // Allowed to add child menu items from admin/structure/menu/manage/footer.
    $request = Request::create('/admin/structure/menu/manage/footer');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertLink('Add child');

    // Allowed to edit menu items.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Allowed to delete menu items from admin/structure/menu/item/%/edit.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertLink('Delete');

    // Allowed to delete menu items from admin/structure/menu/manage/footer.
    $request = Request::create('/admin/structure/menu/manage/footer');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertLink('Delete');

    // Allowed to delete menu items by accessing
    // /admin/structure/menu/item/%/delete directly.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/delete');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

    // Allowed to edit the link of a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldEnabled('link[0][uri]');

    // Allowed to enable/disable a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldEnabled('enabled[value]');

    // Allowed to expand a menu item.
    $request = Request::create('/admin/structure/menu/item/' . $link->id() . '/edit');
    $response = $this->httpKernel->handle($request)->prepare($request);
    $this->setRawContent($response->getContent());
    $this->assertFieldEnabled('expanded[value]');
  }

  /**
   * Assert that a field is disabled.
   *
   * @param string $name
   *   Field name.
   *
   * @see Drupal\Tests\WebAssert::fieldDisabled()
   */
  protected function assertFieldDisabled($name) {
    $fields = $this->cssSelect('input[name="' . $name . '"]');
    $message = strtr('Field with name %name is disabled.', ['%name' => $name]);
    $this->assertNotEmpty($fields, $message);
    $this->assertEquals($fields[0]->attributes()->disabled, 'disabled', $message);
  }

  /**
   * Assert that a field is not disabled.
   *
   * @param string $name
   *   Field name.
   *
   * @see Drupal\Tests\WebAssert::fieldDisabled()
   */
  protected function assertFieldEnabled($name) {
    $fields = $this->cssSelect('input[name="' . $name . '"]');
    $message = strtr('Field with name %name is enabled.', ['%name' => $name]);
    $this->assertNotEmpty($fields, $message);
    $this->assertNotEquals($fields[0]->attributes()->disabled, 'disabled', $message);
  }

  /**
   * Assert that a select field *does* contain a given option.
   *
   * @param string $select_name
   *   Select field name.
   * @param string $option_value
   *   Option value.
   *
   * @see Drupal\Tests\WebAssert::assertOptionByText()
   */
  protected function assertSelectOption($select_name, $option_value) {
    $message = strtr('Select field %select contains an option %option.', [
      '%select' => $select_name,
      '%option' => $option_value,
    ]);
    $options = $this->cssSelect('select[name="' . $select_name . '"] > option');
    $this->assertNotEmpty($options);
    foreach ($options as $option) {
      if ($option->attributes()->value == $option_value) {
        return;
      }
    }
    $this->fail($message);
  }

  /**
   * Assert that a select field does *not* contain a given option.
   *
   * @param string $select_name
   *   Select field name.
   * @param string $option_value
   *   Option value.
   *
   * @see Drupal\Tests\WebAssert::assertOptionByText()
   */
  protected function assertNoSelectOption($select_name, $option_value) {
    $message = strtr('Select field %select does not contain an option %option.', [
      '%select' => $select_name,
      '%option' => $option_value,
    ]);
    $options = $this->cssSelect('select[name="' . $select_name . '"] > option');
    $this->assertNotEmpty($options);
    foreach ($options as $option) {
      if ($option->attributes()->value == $option_value) {
        $this->fail($message);
      }
    }
  }

}
