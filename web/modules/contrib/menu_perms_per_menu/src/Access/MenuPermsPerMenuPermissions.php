<?php

namespace Drupal\menu_perms_per_menu\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;

/**
 * Provides permissions for each menu.
 */
class MenuPermsPerMenuPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of menu permissions.
   *
   * @return array
   *   The menu permissions.
   */
  public function permissions() {
    $perms = [];
    $menus = Menu::loadMultiple();
    foreach ($menus as $menu) {
      $perms['add new links to ' . $menu->id() . ' menu from menu interface'] = [
        'title' => $this->t('Add new links to %menu menu from the menu interface', ['%menu' => $menu->label()]),
      ];
      $perms['delete links in ' . $menu->id() . ' menu from menu interface'] = [
        'title' => $this->t('Delete links in %menu menu from the menu interface', ['%menu' => $menu->label()]),
      ];
      $perms['enable/disable links in ' . $menu->id() . ' menu'] = [
        'title' => $this->t('Enable/disable links in %menu menu', ['%menu' => $menu->label()]),
      ];
      $perms['expand links in ' . $menu->id() . ' menu'] = [
        'title' => $this->t('Expand links in %menu menu', ['%menu' => $menu->label()]),
      ];
      $perms['edit link of menu links in ' . $menu->id() . ' menu'] = [
        'title' => $this->t('Edit link of menu links in %menu menu', ['%menu' => $menu->label()]),
      ];
      $perms['translate links in ' . $menu->id() . ' menu from menu interface'] = [
        'title' => $this->t('Translate links in %menu menu from the menu interface', ['%menu' => $menu->label()]),
      ];
    }
    return $perms;
  }

  /**
   * Checks access to create new menu item.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Drupal\system\Entity\Menu $menu
   *   Run access checks for this menu object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function addItemAccess(AccountInterface $account, Menu $menu) {
    return AccessResult::allowedIfHasPermission($account, 'add new links to ' . $menu->id() . ' menu from menu interface');
  }

  /**
   * Checks access to delete a menu item.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link_content
   *   Run access checks for this menu link object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function deleteItemAccess(AccountInterface $account, ?MenuLinkContent $menu_link_content = NULL) {
    if (!$menu_link_content) {
      return AccessResult::neutral();
    }
    return AccessResult::allowedIfHasPermission($account, 'delete links in ' . $menu_link_content->getMenuName() . ' menu from menu interface');
  }

  /**
   * Checks access to menu item translation.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link_content
   *   Run access checks for this menu link object.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function translateItemAccess(AccountInterface $account, ?MenuLinkContent $menu_link_content = NULL) {
    if (!$menu_link_content) {
      return AccessResult::neutral();
    }
    return AccessResult::allowedIfHasPermission($account, 'translate links in ' . $menu_link_content->getMenuName() . ' menu from menu interface');
  }

}
