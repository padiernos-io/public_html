<?php

namespace Drupal\config_patch\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class ConfigPatchUtilities.
 *
 * @package Drupal\config_patch\Controller
 */
class ConfigPatchUtilities extends ControllerBase {

  /**
   * Invalidation service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagInvalidator;

  /**
   * ConfigSubscriber constructor.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cacheTagsInvalidator
   *   Invalidation service.
   */
  public function __construct(CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    $this->cacheTagInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache_tags.invalidator')
    );  
  }

  /**
   * Clear the config change cache.
   * 
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   */
  public function clearCache(Request $request) {
    $destination = $request->get('destination');
    $this->cacheTagInvalidator->invalidateTags(['config_patch']);
    return new RedirectResponse($destination);
  }

}
