<?php

namespace Drupal\entityqueue_buttons;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;

/**
 * Service for managing EntityQueue Buttons functionality.
 */
class EntityQueueButtonsManager {
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The CSRF token generator.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * Constructs a EntityQueueButtonsManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   The CSRF token generator.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    CsrfTokenGenerator $csrf_token,
    TranslationInterface $string_translation,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->csrfToken = $csrf_token;
    $this->setStringTranslation($string_translation);
  }

  /**
   * Gets enabled queues for a content type.
   *
   * @param string $content_type
   *   The content type ID.
   *
   * @return array
   *   Array of enabled queue IDs.
   */
  public function getEnabledQueues($content_type) {
    $config = $this->configFactory->get('entityqueue_buttons.settings');
    $queue_settings = $config->get("queue_settings.$content_type");

    // If there are no settings or they're not in array form, return empty array.
    if (!is_array($queue_settings)) {
      return [];
    }

    // Filter out unchecked values (those set to 0)
    return array_filter($queue_settings, function ($value) {
      return $value !== 0;
    });
  }

  /**
   * Gets queue operation links for a node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node entity.
   *
   * @return array
   *   Array of render arrays for links.
   */
  public function getQueueLinks($node) {
    $links = [];
    $enabled_queues = $this->getEnabledQueues($node->bundle());

    foreach ($enabled_queues as $queue_id) {
      if (!\Drupal::currentUser()->hasPermission('manipulate ' . $queue_id . ' entityqueue')) {
        continue;
      }

      $queue = $this->entityTypeManager->getStorage('entity_queue')->load($queue_id);
      if (!$queue) {
        continue;
      }

      $is_queued = $this->isItemInQueue($queue_id, $node->id());

      $operation = $is_queued ? 'remove' : 'add';
      $label = $is_queued ?
        $this->t('Remove from @queue', ['@queue' => $queue->label()]) :
        $this->t('Add to @queue', ['@queue' => $queue->label()]);

      $url = Url::fromRoute('entityqueue_buttons.queue_operation', [
        'node' => $node->id(),
        'queue' => $queue_id,
        'operation' => $operation,
      ]);

      $options = [
        'attributes' => [
          'class' => ['use-ajax', 'entityqueue-button', "entityqueue-$operation"],
        ],
      ];

      $url->setOptions($options);

      $links[$queue_id] = Link::fromTextAndUrl($label, $url)->toRenderable();
    }

    return $links;
  }

  /**
   * Checks if an item is in a queue.
   *
   * @param string $queue_id
   *   The queue ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return bool
   *   TRUE if the item is in the queue.
   */
  public function isItemInQueue($queue_id, $entity_id) {
    $subqueue = $this->getSubqueue($queue_id);
    if (!$subqueue) {
      return FALSE;
    }

    $items = $subqueue->get('items')->getValue();
    foreach ($items as $item) {
      if ($item['target_id'] == $entity_id) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Gets the subqueue for a queue.
   *
   * @param string $queue_id
   *   The queue ID.
   *
   * @return \Drupal\entityqueue\Entity\EntitySubqueue|null
   *   The subqueue entity or NULL.
   */
  public function getSubqueue($queue_id) {
    $queue_storage = $this->entityTypeManager->getStorage('entity_queue');
    $subqueue_storage = $this->entityTypeManager->getStorage('entity_subqueue');

    $queue = $queue_storage->load($queue_id);
    if (!$queue) {
      return NULL;
    }

    $subqueues = $subqueue_storage->loadByProperties(['queue' => $queue_id]);
    return reset($subqueues);
  }

  /**
   * Adds an item to a queue.
   *
   * @param string $queue_id
   *   The queue ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return bool
   *   TRUE if the item was added successfully, FALSE otherwise.
   */
  public function addToQueue($queue_id, $entity_id) {
    $subqueue = $this->getSubqueue($queue_id);
    if (!$subqueue) {
      return FALSE;
    }

    if (!$this->isItemInQueue($queue_id, $entity_id)) {
      try {
        // Load the node entity.
        $node = $this->entityTypeManager->getStorage('node')->load($entity_id);
        if (!$node) {
          return FALSE;
        }

        // Add the entity to the START of the subqueue.
        $items = $subqueue->get('items')->getValue();
        array_unshift($items, ['target_id' => $entity_id]);
        $subqueue->set('items', $items);
        $subqueue->save();

        return TRUE;
      }
      catch (\Exception $e) {
        \Drupal::logger('entityqueue_buttons')->error('Failed to add item to queue: @message', [
          '@message' => $e->getMessage(),
        ]);
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Removes an item from a queue.
   *
   * @param string $queue_id
   *   The queue ID.
   * @param int $entity_id
   *   The entity ID.
   *
   * @return bool
   *   TRUE if the item was removed successfully, FALSE otherwise.
   */
  public function removeFromQueue($queue_id, $entity_id) {
    $subqueue = $this->getSubqueue($queue_id);
    if (!$subqueue) {
      return FALSE;
    }

    try {
      $items = $subqueue->get('items')->getValue();
      foreach ($items as $delta => $item) {
        if ($item['target_id'] == $entity_id) {
          unset($items[$delta]);
          $subqueue->set('items', array_values($items));
          $subqueue->save();
          return TRUE;
        }
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('entityqueue_buttons')->error('Failed to remove item from queue: @message', [
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Gets all available entity queues.
   *
   * @return \Drupal\entityqueue\Entity\EntityQueue[]
   *   Array of entity queue entities.
   */
  public function getAllQueues() {
    return $this->entityTypeManager->getStorage('entity_queue')->loadMultiple();
  }

}