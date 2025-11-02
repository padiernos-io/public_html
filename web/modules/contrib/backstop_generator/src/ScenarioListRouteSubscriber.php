<?php

namespace Drupal\backstop_generator;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Disables the access to the add form of the Backstop Scenario entity.
 *
 * The creation of Scenario entities is not allowed through the UI, as they are
 * generated programmatically by the Backstop Scenario Generator when creating
 * Profile entities.
 */
class ScenarioListRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritDoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Get the route for your entity add form.
    $route = $collection->get('entity.backstop_scenario.add_form');
    if ($route) {
      // Set access to FALSE.
      $route->setRequirement('_access', 'FALSE');
    }
  }

}
