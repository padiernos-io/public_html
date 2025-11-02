<?php

namespace Drupal\patreon_user\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\patreon\PatreonServiceInterface;
use Drupal\patreon_user\PatreonUserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * A Controller for the Patreon User endpoint.
 *
 * @package Drupal\patreon_user\Controller
 */
class PatreonUserController extends ControllerBase {

  /**
   * Creates the controller.
   *
   * @param \Drupal\patreon\PatreonServiceInterface $service
   *   A Patreon API service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   *   The request stack service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger channel.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   A Config Factory.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user account.
   */
  public function __construct(
    protected readonly PatreonServiceInterface $service,
    protected readonly RequestStack $stack,
    protected readonly LoggerInterface $logger,
    ConfigFactoryInterface $configFactory,
    AccountInterface $currentUser,
  ) {
    $this->configFactory = $configFactory;
    $this->currentUser = $currentUser;
  }

  /**
   * Create function.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Dependency Injection Container.
   *
   * @return \Drupal\patreon_user\Controller\PatreonUserController
   *   The Controller interface.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('patreon_user.api'),
      $container->get('request_stack'),
      $container->get('logger.factory')->get('patreon_user'),
      $container->get('config.factory'),
      $container->get('current_user'),
    );
  }

  /**
   * Logs user in from Patreon Oauth redirect return.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects user to /user or 404s.
   */
  public function oauth(): RedirectResponse {
    $config = $this->configFactory->getEditable('patreon_user.settings');
    $settings = $config->get('patreon_user_registration');
    $route_name = '<front>';
    $route_params = [];

    if ($settings != PatreonUserService::PATREON_USER_NO_LOGIN) {
      if ($code = $this->stack->getCurrentRequest()->query->get('code')) {
        if ($this->currentUser->isAnonymous()) {
          try {
            if ($tokens = $this->service->tokensFromCode($code)) {
              $this->service->setToken($tokens);
              if ($patreon_data = $this->service->fetchUser()) {
                if ($this->service->canLogin($patreon_data)) {
                  if ($account = $this->service->getUser($patreon_data)) {
                    $this->service->storeTokens($tokens, $account);

                    if (!$account->isBlocked()) {
                      $this->service->assignRoles($account, $patreon_data);
                      $login_method = $config->get('patreon_user_login_method');

                      // If we have a return path to send people to.
                      if ($path = $this->service->getReturnPath()) {
                        $route_name = $path['route_name'];
                        $route_params = $path['route_parameters'];
                      }

                      if ($login_method == PatreonUserService::PATREON_USER_SINGLE_SIGN_ON) {
                        user_login_finalize($account);
                      }
                      else {
                        $mail = _user_mail_notify('password_reset', $account);
                        if (!empty($mail)) {
                          $this->messenger()->addError($this->t('Further instructions have been sent to your email address.'));
                        }
                      }
                    }
                    else {
                      $user_config = $this->configFactory->get('user.settings');
                      if ($user_config->get('verify_mail') && $account->isNew()) {
                        $this->messenger()->addStatus($this->t('Further instructions have been sent to your email address.'));
                      }
                      else {
                        $this->messenger()->addError($this->t('Your account is blocked. Please contact an administrator.'));
                      }
                    }
                  }
                  else {
                    $this->messenger()->addError($this->t('There was a problem creating your account. Please contact an administrator.'));
                  }
                }
                else {
                  $message = ($settings == PatreonUserService::PATREON_USER_ONLY_PATRONS) ? $this->t('Only patrons may log in via Patreon.') : $this->t('Log on via Patreon is not enabled at present.');
                  $message .= ' ' . $this->t('Please contact an administrator if you feel this is in error.');
                  $this->messenger()->addError($message);
                }
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
        }
      }
    }

    return $this->redirect($route_name, $route_params);
  }

}
