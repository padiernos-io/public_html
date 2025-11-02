<?php

namespace Drupal\menu_perms_per_menu\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class MenuPermsPerMenuRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $routes = $collection->all();
    foreach ($routes as $route_name => $route) {
      switch ($route_name) {

        case 'entity.menu.collection':
          $route->setDefaults([
            '_title' => $route->getDefault('_title'),
            '_controller' => '\Drupal\menu_perms_per_menu\Controller\MenuPermsPerMenuController::menuOverviewPage',
          ]);
          break;

        case 'entity.menu.add_link_form':
          $route->setRequirements(['_custom_access' => '\Drupal\menu_perms_per_menu\Access\MenuPermsPerMenuPermissions::addItemAccess']);
          break;

        case 'entity.menu_link_content.delete_form':
          $route->setRequirements(['_custom_access' => '\Drupal\menu_perms_per_menu\Access\MenuPermsPerMenuPermissions::deleteItemAccess']);
          break;

        case 'entity.menu_link_content.content_translation_overview':
        case 'entity.menu_link_content.content_translation_add':
          $route->setRequirements(['_custom_access' => '\Drupal\menu_perms_per_menu\Access\MenuPermsPerMenuPermissions::translateItemAccess']);
          break;

      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run after menu_admin_per_menu, which has priority -220.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -225];
    return $events;
  }

}
