<?php

namespace Drupal\entityqueue_buttons\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entityqueue_buttons\EntityQueueButtonsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for queue operations.
 */
class EntityQueueButtonsController extends ControllerBase {
  /**
   * The queue manager service.
   *
   * @var \Drupal\entityqueue_buttons\EntityQueueButtonsManager
   */
  protected $queueManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a EntityQueueButtonsController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\entityqueue_buttons\EntityQueueButtonsManager $queue_manager
   *   The queue manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityQueueButtonsManager $queue_manager,
    RequestStack $request_stack,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->queueManager = $queue_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('entity_type.manager'),
          $container->get('entityqueue_buttons.queue_manager'),
          $container->get('request_stack')
      );
  }

  /**
   * Checks access for queue operations.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check access for.
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node entity.
   * @param string $queue
   *   The queue ID.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, ?EntityInterface $node = NULL, $queue = NULL) {
    if (!$node || !$queue) {
      return AccessResult::forbidden();
    }

    // Check if user has permission to manipulate this queue.
    return AccessResult::allowedIf($account->hasPermission('manipulate ' . $queue . ' entityqueue'));
  }

  /**
   * Handles queue operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node entity.
   * @param string $queue
   *   The queue ID.
   * @param string $operation
   *   The operation (add|remove).
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function queueOperation(EntityInterface $node, $queue, $operation) {
    $response = new AjaxResponse();

    try {
      // Check if the queue exists.
      $queue_entity = $this->entityTypeManager->getStorage('entity_queue')->load($queue);
      if (!$queue_entity) {
        throw new \Exception($this->t('Queue not found'));
      }

      // Perform the operation.
      if ($operation === 'add') {
        $success = $this->queueManager->addToQueue($queue, $node->id());
        $message = $success ?
                $this->t('Added to @queue', ['@queue' => $queue_entity->label()]) :
                $this->t('Failed to add to @queue', ['@queue' => $queue_entity->label()]);
      }
      else {
        $success = $this->queueManager->removeFromQueue($queue, $node->id());
        $message = $success ?
                $this->t('Removed from @queue', ['@queue' => $queue_entity->label()]) :
                $this->t('Failed to remove from @queue', ['@queue' => $queue_entity->label()]);
      }

      // Get updated buttons markup.
      $links = $this->queueManager->getQueueLinks($node);
      $build = [
        '#theme' => 'entityqueue_buttons',
        '#links' => $links,
      ];

      // Add commands to update the UI.
      $response->addCommand(
            new ReplaceCommand(
                '.entityqueue-buttons-wrapper',
                $build
            )
        );

      $response->addCommand(
            new MessageCommand(
                $message,
                NULL,
                [
                  'type' => $success ? 'status' : 'error',
                ]
            )
        );
    }
    catch (\Exception $e) {
      \Drupal::logger('entityqueue_buttons')->error('Error processing queue operation: @message', [
        '@message' => $e->getMessage(),
      ]);
      $response->addCommand(
            new MessageCommand(
                $this->t('An error occurred while processing your request.'),
                NULL,
                ['type' => 'error']
            )
            );
    }

    return $response;
  }

}