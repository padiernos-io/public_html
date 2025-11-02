<?php

namespace Drupal\url_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service to extract entities from URLs.
 */
interface UrlEntityExtractorInterface {

  /**
   * Extracts the entity from the current page.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   The request object to extract the entity from.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity or NULL if no entity could be extracted.
   */
  public function getCurrentEntity(?Request $request = NULL): ?EntityInterface;

  /**
   * Extracts the entity from the referring page.
   *
   * This method uses the Referer header to extract the entity. The Referer
   * header might not be present, depending on the requesting client.
   *
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   *   The request object to extract the entity from.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity or NULL if no entity could be extracted.
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referer
   */
  public function getRefererEntity(?Request $request = NULL): ?EntityInterface;

  /**
   * Extracts the entity from the given route.
   *
   * @param string $routeName
   *   The route name.
   * @param array $routeParameters
   *   The route parameters.
   * @param array $options
   *   An array of URL options. See \Drupal\Core\Url::fromUri() for details.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity or NULL if no entity could be extracted.
   */
  public function getEntityByRoute(string $routeName, array $routeParameters = [], array $options = []): ?EntityInterface;

  /**
   * Extracts the entity from the given URL.
   *
   * @param \Drupal\Core\Url $url
   *   The URL object.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity or NULL if no entity could be extracted.
   */
  public function getEntityByUrl(Url $url): ?EntityInterface;

}
