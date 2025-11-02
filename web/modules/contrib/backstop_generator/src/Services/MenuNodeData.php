<?php

namespace Drupal\backstop_generator\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Service description.
 */
class MenuNodeData {

  /**
   * The list of menu paths.
   *
   * @var array
   */
  protected $menuPaths = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translation;

  /**
   * Constructs a new MenuNodeData object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   The menu link tree service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    MenuLinkTreeInterface $menu_link_tree,
    MessengerInterface $messenger,
    TranslationInterface $translation,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkTree = $menu_link_tree;
    $this->messenger = $messenger;
    $this->translation = $translation;
  }

  /**
   * Returns the paths for all the links in a given menu up to the given depth.
   *
   * @param string $menu_id
   *   Value of the menu ID.
   * @param int $level
   *   Menu level.
   *
   * @return array
   *   Return an array of menu paths from the menu
   */
  public function getMenuLinkPaths($menu_id, $level = 1) {
    // Ensure the level is at least 1.
    $level = max(1, (int) $level);

    // Load the menu tree service.
    $menu_tree = $this->menuLinkTree;
    // Create an instance of MenuTreeParameters.
    $parameters = new MenuTreeParameters();
    $parameters->minDepth = 1;
    $parameters->maxDepth = $level;

    // Load the menu tree based on the parameters.
    $tree = $menu_tree->load($menu_id, $parameters);

    // Transform the tree into a renderable array.
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    // Traverse the tree and collect menu paths.
    $paths = [];
    foreach ($tree as $element) {
      $paths = array_merge($paths, $this->collectMenuPathsFromTree($element));
    }
    return $paths;
  }

  /**
   * Helper function to recursively collect paths from a menu tree element.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $element
   *   The menu tree element to process.
   *
   * @return array
   *   An array of paths collected from the menu tree element.
   */
  protected function collectMenuPathsFromTree(MenuLinkTreeElement $element) {
    $menuPaths = [];

    // Get the alias/path for the current menu item.
    if ($element->link->getUrlObject() instanceof Url) {
      $route_parameter_object = $element->link->getUrlObject();

      // Ignore external, empty and disabled menu links; inform the user why.
      if (!$element->link->isEnabled()) {
        $link_title = $element->link->getTitle();
        $message = t("MENU LINK IGNORED: The %title menu link was ignored because it is disabled.", ['%title' => $link_title]);
        $this->messenger->addStatus($message);
      }
      // Handle paths being redirected.
      elseif (!$route_parameter_object->isRouted() && !$route_parameter_object->isExternal()) {
        $menuPaths[] = [
          'node' => 'redirect_url',
          'title' => $element->link->getTitle(),
          'path' => $element->link->getUrlObject()->toString(),
          'bundle' => 'redirect_url',
        ];
      }
      elseif (
        $route_parameter_object->isExternal() ||
        $route_parameter_object->getRouteName() == '<nolink>'
      ) {
        $link_title = $element->link->getTitle();
        $status = $route_parameter_object->isExternal() ?
          'links to an external website.' :
          'is routed to <nolink>.';

        $message = t(
          "MENU LINK IGNORED: The %title menu link was ignored because it %status.",
          ['%title' => $link_title, '%status' => $status]
        );
        $this->messenger->addStatus($message);

        return [];
      }
      else {
        // Retrieve the bundle type.
        $route_params = $route_parameter_object->getRouteParameters();
        if (isset($route_params['node'])) {
          $node_id = (int) $route_params['node'];
          $node = Node::load($node_id);
          $bundle = $node->bundle();
        }

        // Create the data array.
        $alias = $element->link->getUrlObject()->toString();
        $title = $element->link->getTitle();
        $menuPaths[] = [
          'nid' => $node_id ?? NULL,
          'title' => $title,
          'path' => $alias,
          'bundle' => $bundle ?? NULL,
        ];
      }
    }

    // Recursively collect paths from child elements.
    if (!empty($element->subtree)) {
      foreach ($element->subtree as $child) {
        $menuPaths = array_merge($menuPaths, $this->collectMenuPathsFromTree($child));
      }
    }

    return $menuPaths;
  }

}
