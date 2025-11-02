<?php

namespace Drupal\menu_link_content_visibility;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Component\Plugin\Exception\MissingValueContextException;
use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Menu\DefaultMenuLinkTreeManipulators;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\menu_link_content\Plugin\Menu\MenuLinkContent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the access control handler for the menu item.
 */
class MenuLinkContentVisibilityLinkTreeManipulator extends DefaultMenuLinkTreeManipulators {

  use ConditionAccessResolverTrait;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionManager;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The global context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * The global entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a MenuLinkContentVisibilityTreeManipulator object.
   *
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition plugin manager.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The plugin context handler.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The global context repository service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The global entity repository service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(AccessManagerInterface $access_manager, AccountInterface $account, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $moduleHandler, ExecutableManagerInterface $condition_manager, ContextHandlerInterface $context_handler, ContextRepositoryInterface $context_repository, EntityRepositoryInterface $entity_repository, RequestStack $request_stack) {
    parent::__construct($access_manager, $account, $entity_type_manager, $moduleHandler);
    $this->conditionManager = $condition_manager;
    $this->contextHandler = $context_handler;
    $this->contextRepository = $context_repository;
    $this->entityRepository = $entity_repository;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  protected function menuLinkCheckAccess(MenuLinkInterface $instance) {
    $access_result = parent::menuLinkCheckAccess($instance);

    // Break out early if a menu administrator is running the menu access check,
    // or if we are not handling a content menu link.
    $is_menu_admin = $this->getRequest()->attributes->get('_menu_admin') ?? FALSE;
    if ($is_menu_admin || !$instance instanceof MenuLinkContent) {
      return $access_result;
    }

    // Load the menu link entity.
    // @todo replace with $instance->getEntity() when no longer protected.
    // @see https://www.drupal.org/project/drupal/issues/2997790
    $uuid = $instance->getDerivativeId();
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $entity */
    $entity = $this->entityRepository
      ->loadEntityByUuid('menu_link_content', $uuid);

    // Load visibility conditions - if any.
    $visibility_value = $entity->get('visibility')->value;
    if (!empty($visibility_value)) {
      $visibility = unserialize($visibility_value);
    }
    if (empty($visibility)) {
      return $access_result;
    }

    $condition_plugin_collection = new ConditionPluginCollection($this->conditionManager, $visibility);
    $conditions = [];
    $missing_context = FALSE;
    $missing_value = FALSE;
    foreach ($condition_plugin_collection as $condition_id => $condition) {
      // gtag module does something special with contexts, ignore them for now
      if (in_array($condition_id, ['gtag_domain', 'gtag_language'])) {
        continue;
      }

      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          $this->contextHandler->applyContextMapping($condition, $contexts);
          $conditions[$condition_id] = $condition;
        }
        catch (MissingValueContextException $e) {
          $missing_value = TRUE;
        }
        catch (ContextException $e) {
          $missing_context = TRUE;
        }
      }
    }

    if ($missing_context) {
      // If any context is missing then we might be missing cacheable
      // metadata, and don't know based on what conditions the menu link is
      // accessible or not. Make sure the result cannot be cached.
      $access_result = AccessResult::forbidden()->setCacheMaxAge(0);
    }
    elseif ($missing_value) {
      // The contexts exist but have no value. Deny access without
      // disabling caching. For example the node type condition will have a
      // missing context on any non-node route like the frontpage.
      $access_result = AccessResult::forbidden();
    }
    elseif ($this->resolveConditions($conditions, 'and') === FALSE) {
      $reason = count($conditions) > 1
        ? "One of the menu link visibility conditions ('%s') denied access."
        : "The menu link visibility condition '%s' denied access.";
      $access_result = AccessResult::forbidden(sprintf($reason, implode("', '", array_keys($conditions))));
    }

    $this->mergeCacheabilityFromConditions($access_result, $conditions);

    // Ensure that access is evaluated again when the menu link changes.
    return $access_result->addCacheableDependency($entity);
  }

  /**
   * Gets the request object.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   The request object.
   */
  protected function getRequest() {
    return $this->requestStack->getCurrentRequest();
  }

  /**
   * Merges cacheable metadata from conditions.
   *
   * @param \Drupal\Core\Access\AccessResultInterface $access_result
   *   The access result object.
   * @param \Drupal\Core\Condition\ConditionInterface[] $conditions
   *   List of visibility conditions.
   */
  protected function mergeCacheabilityFromConditions(AccessResultInterface $access_result, array $conditions) {
    foreach ($conditions as $condition) {
      if ($condition instanceof CacheableDependencyInterface) {
        if ($access_result instanceof RefinableCacheableDependencyInterface) {
          $access_result->addCacheTags($condition->getCacheTags());
          $access_result->addCacheContexts($condition->getCacheContexts());
        }
        $access_result->setCacheMaxAge(Cache::mergeMaxAges($access_result->getCacheMaxAge(), $condition->getCacheMaxAge()));
      }
    }
  }

}
