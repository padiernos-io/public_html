<?php

namespace Drupal\menu_perms_per_menu\Controller;

use Drupal\menu_admin_per_menu\Controller\MenuAdminPerMenuController;

/**
 * Extends menu_admin_per_menus controller with our own permissions.
 */
class MenuPermsPerMenuController extends MenuAdminPerMenuController {

  /**
   * Alters menus overview page (admin/structure/menu).
   */
  public function menuOverviewPage() {
    $menu_table = parent::menuOverviewPage();
    $account = $this->currentUser();
    foreach ($menu_table['table']['#rows'] as $menu_key => $menu_item) {
      if (!$account->hasPermission("add new links to $menu_key menu from menu interface")) {
        unset($menu_table['table']['#rows'][$menu_key]['operations']['data']['#links']['add']);
      }
    }
    return $menu_table;
  }

}
