<?php

namespace Drupal\patreon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\State\StateInterface;
use Drupal\patreon\PatreonServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A Controller for the Oauth endpoint.
 *
 * @package Drupal\patreon\Controller
 */
class PatreonController extends ControllerBase {

  /**
   * Creates the controller.
   *
   * @param \Drupal\patreon\PatreonServiceInterface $service
   *   A Patreon API service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   *   The request stack service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger channel.
   * @param \Drupal\Core\State\StateInterface $state
   *   A State service.
   */
  public function __construct(
    protected readonly PatreonServiceInterface $service,
    protected readonly RequestStack $stack,
    protected readonly LoggerInterface $logger,
    protected readonly StateInterface $state,
  ) {
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Dependency Injection Container.
   *
   * @return \Drupal\patreon\Controller\PatreonController
   *   The Controller interface.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patreon.api'),
      $container->get('request_stack'),
      $container->get('logger.factory')->get('patreon'),
      $container->get('state')
    );
  }

  /**
   * Patreon oauth callback.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects to the Patreon settings form.
   */
  public function oauth(): RedirectResponse {

    // Get the variables we need.
    $code = $this->stack->getCurrentRequest()->query->get('code');
    $state = $this->stack->getCurrentRequest()->query->get('state');

    $session = $this->stack->getCurrentRequest()->getSession();
    $session_state = $session->get('oauth2state');

    // Now we have what we need, we can clear out the session for security
    // reasons.
    $session->remove('oauth2state');

    if (!$code || !$state || $state !== $session_state) {

      // If anything is missing, let's get out of here.
      $this->logger->critical($this->t('Could not authenticate Oauth2 return for Patreon API access.'));
      return $this->redirect('<front>');
    }

    try {
      $tokens = $this->service->tokensFromCode($code);
      $this->service->storeTokens($tokens);

      if ($return = $this->service->fetchUser()) {
        if ($id = $this->service->getValueByKey($return, [
          'data',
          'id',
        ])) {
          $this->state->set('patreon.creator_id', $id);
          $this->service->storeCampaigns();
        }
      }
    }
    catch (\Exception $e) {
      $message = $this->t('The Patreon API returned the following error: :error', [
        ':error' => $e->getMessage(),
      ]);
      $this->logger->error($message);
      $this->messenger()->addError($message);
    }

    return $this->redirect('patreon.settings_form');
  }

}
