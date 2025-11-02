<?php

namespace Drupal\backstop_generator\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service description.
 */
class RandomNodeList {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RandomNodeList object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Fetches random nodes of a specified content type.
   *
   * @param string $content_type_id
   *   The content type machine name.
   * @param int $quantity
   *   The number of random nodes to fetch.
   *
   * @return array
   *   An array of associative arrays containing node IDs and titles.
   */
  public function getRandomNodes(string $content_type_id, int $quantity): array {
    // Create an entity query to get all published nodes of
    // the specified content type.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', $content_type_id)
      ->condition('status', 1)
      ->accessCheck(TRUE);

    // Execute the query to get all node IDs.
    $nids = $query->execute();

    if (empty($nids)) {
      return [];
    }

    // Randomly shuffle the node IDs.
    $nids = array_values($nids);
    shuffle($nids);

    // Limit the array to the specified quantity.
    $selected_nids = array_slice($nids, 0, $quantity);

    // Load the nodes based on the selected IDs.
    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($selected_nids);

    // Extract the node ID and title.
    $results = [];
    foreach ($nodes as $node) {
      $results[] = [
        'nid' => $node->id(),
        'title' => $node->getTitle(),
        'bundle' => $node->bundle(),
      ];
    }

    return $results;
  }

}
