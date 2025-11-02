<?php

declare(strict_types=1);

namespace Drupal\reassign_user_content\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a Reassign selected content to user action.
 */
#[Action(
  id: 'reassign_user_content_action',
  label: new TranslatableMarkup('Reassign selected content to user'),
  type: 'node',
)]
final class ReassignUserContentAction extends ActionBase implements ContainerFactoryPluginInterface {

  use LoggerChannelTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly PrivateTempStoreFactory $tempstorePrivate,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tempstore.private'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities): void {
    // Store selected nodes in tempstore and redirect to form.
    $tempstore = $this->tempstorePrivate->get('reassign_user_content');
    try {
      $tempstore->set('selected_nodes', array_map(fn($e) => (int) $e->id(), $entities));
    }
    catch (TempStoreException $e) {
      $message = $this->t('Failed to store selected nodes in tempstore: @message', ['@message' => $e->getMessage()]);
      $this->getLogger('reassign_user_content')->error($message);
      $this->messenger()->addError($message);

      return;
    }

    $response = new RedirectResponse(Url::fromRoute('reassign_user_content.reassign_author')->toString());
    $response->send();
    exit;
  }

  /**
   * {@inheritdoc}
   */
  public function access($node, ?AccountInterface $account = NULL, $return_as_object = FALSE): AccessResultInterface|bool {
    /** @var \Drupal\node\NodeInterface $node */
    $access = $node->access('update', $account, TRUE)
      ->andIf($node->get('uid')->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    // Leave empty, as redirection will handle logic.
  }

}
