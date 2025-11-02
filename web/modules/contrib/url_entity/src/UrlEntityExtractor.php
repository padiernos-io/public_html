<?php

namespace Drupal\url_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Route;

/**
 * Service to extract entities from URLs.
 */
class UrlEntityExtractor implements UrlEntityExtractorInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * The request matcher.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  protected RequestMatcherInterface $requestMatcher;

  /**
   * Constructs a new UrlEntityExtractor object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Symfony\Component\Routing\Matcher\RequestMatcherInterface $requestMatcher
   *   The request matcher.
   */
  public function __construct(
    RequestStack $requestStack,
    RequestMatcherInterface $requestMatcher
  ) {
    $this->requestStack = $requestStack;
    $this->requestMatcher = $requestMatcher;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentEntity(?Request $request = NULL): ?EntityInterface {
    $request ??= $this->requestStack->getCurrentRequest();
    return $this->getEntityByRequest($request);
  }

  /**
   * {@inheritdoc}
   */
  public function getRefererEntity(?Request $request = NULL): ?EntityInterface {
    $request ??= $this->requestStack->getCurrentRequest();
    $refererUrl = $request->server->get('HTTP_REFERER');
    if ($refererUrl === NULL) {
      return NULL;
    }

    $refererRequest = $this->createRequest($refererUrl);
    if ($refererRequest === NULL) {
      return NULL;
    }

    return $this->getEntityByRequest($refererRequest);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityByRoute(string $routeName, array $routeParameters = [], array $options = []): ?EntityInterface {
    $url = Url::fromRoute($routeName, $routeParameters, $options);
    return $this->getEntityByUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityByUrl(Url $url): ?EntityInterface {
    $request = $this->createRequest($url->setAbsolute()->toString());
    if ($request === NULL) {
      return NULL;
    }

    return $this->getEntityByRequest($request);
  }

  /**
   * Gets an entity from a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or NULL if none could be extracted.
   */
  protected function getEntityByRequest(Request $request): ?EntityInterface {
    $route = $request->attributes->get('_route_object');
    if (!$route instanceof Route) {
      return NULL;
    }

    $routeName = $request->attributes->get('_route');
    if (!is_string($routeName)) {
      return NULL;
    }

    // Attempt to extract entity information from relevant route parameters.
    $parameterName = NULL;
    $entity = NULL;

    if ($entityForm = $route->getDefault('_entity_form')) {
      [$parameterName] = explode('.', $entityForm);
    }
    elseif ($entityAccess = $route->getRequirement('_entity_access')) {
      [$parameterName] = explode('.', $entityAccess);
    }
    elseif ($routeName === 'entity.menu.add_link_form') {
      $parameterName = 'menu';
    }

    if ($parameterName !== NULL && $request->attributes->has($parameterName)) {
      $entity = $request->attributes->get($parameterName);
    }

    // Attempt to extract any loaded entity from route parameters.
    if (!$entity instanceof EntityInterface) {
      foreach ($request->attributes as $value) {
        if ($value instanceof EntityInterface) {
          $entity = $value;
        }
      }
    }

    return $entity;
  }

  /**
   * Creates a request object from a URL.
   *
   * @param string $url
   *   The URL.
   *
   * @return \Symfony\Component\HttpFoundation\Request|null
   *   A fully built request object, or NULL if the URL couldn't be matched.
   */
  protected function createRequest(string $url): ?Request {
    try {
      $request = Request::create($url);
      $attributes = $this->requestMatcher->matchRequest($request);
      $request->attributes->add($attributes);
    }
    catch (ParamNotConvertedException $e) {
      return NULL;
    }
    catch (ResourceNotFoundException $e) {
      return NULL;
    }
    catch (MethodNotAllowedException $e) {
      return NULL;
    }
    catch (AccessDeniedHttpException $e) {
      return NULL;
    }
    catch (NotFoundHttpException $e) {
      return NULL;
    }

    return $request;
  }

}
