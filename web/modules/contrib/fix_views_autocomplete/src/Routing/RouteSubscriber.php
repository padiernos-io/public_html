<?php

namespace Drupal\fix_views_autocomplete\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Alters existing routes.
   *
   * This method changes the requirement for the "view_args" parameter on the
   * "views_filters.autocomplete" route so that an empty string is allowed.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection.
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('views_filters.autocomplete')) {
      // Allow the view_args parameter to be empty.
      $route->setRequirement('view_args', '[^/]*');
      // Set a default value (empty string) to be safe.
      $route->setDefault('view_args', '');
    }
  }
}