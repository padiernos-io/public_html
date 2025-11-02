<?php

namespace Drupal\opcachectl\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PHP OPcache control.
 */
class OpcacheCtlController extends ControllerBase {

  /**
   * Logger channel.
   */
  protected LoggerChannelInterface $logger;

  /**
   * Constructs a new OpcacheCtlController object.
   *
   * @param LoggerChannelFactoryInterface $logger
   *   Logger factory to use.
   */
  public function __construct(LoggerChannelFactoryInterface $logger) {
    $this->logger = $logger->get('opcachectl');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory'),
    );
  }

  /**
   * Create JSON response to be used by opcachectl "API" routes.
   *
   * May help to keep responses consistent over various routes.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The Response.
   */
  protected function createControlResponse(array $data, $status = Response::HTTP_OK): JsonResponse {
    $data['host'] = gethostname();
    $data['address'] = $_SERVER['SERVER_ADDR'];
    $data['timestamp'] = $_SERVER['REQUEST_TIME_FLOAT'];
    return new JsonResponse($data, $status);
  }

  /**
   * Request current OPcache status.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON Response describing current OPcache status.
   */
  public function controlGet(Request $request): JsonResponse {
    if (!function_exists('opcache_get_status')) {
      return $this->createControlResponse(['error' => 'PHP OPcache not enabled'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    return $this->createControlResponse(['status' => opcache_get_status(FALSE)]);
  }

  /**
   * Request to reset OPcache.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response describing operation error or current OPcache status.
   */
  public function controlPurge(Request $request): JsonResponse {
    if (!function_exists('opcache_get_status')) {
      return $this->createControlResponse(['error' => 'PHP OPcache not enabled'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    $request_path = $request->getPathInfo();
    $request_method = $request->getMethod();
    $this->logger->debug('PHP OPcache reset call via ' . $request_method . ' ' . $request_path . ' on host ' . gethostname());
    if (opcachectl_reset()) {
      return $this->createControlResponse(['status' => opcache_get_status(FALSE)]);
    }
    else {
      return $this->createControlResponse(['error' => 'opcache_reset() Failed'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
  }

}
