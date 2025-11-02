<?php

namespace Drupal\patreon;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Link;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use HansPeterOrding\OAuth2\Client\Provider\Patreon;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Patreon\API;
use Drupal\user\UserInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service to connect to the Patreon API.
 *
 * @package Drupal\patreon
 */
class PatreonService implements PatreonServiceInterface {

  use StringTranslationTrait;

  /**
   * Config for the service.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * Watchdog logger channel for captcha.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a ParagraphsTypeIconUuidLookup instance.
   *
   * @param \Drupal\Core\Path\CurrentPathStack $path
   *   The Drupal Path service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   A Drupal Config Factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   A logger channel.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   An Entity Type Manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $stack
   *   The request stack service.
   * @param \Drupal\Core\State\StateInterface $stateApi
   *   A state service.
   */
  public function __construct(
    protected readonly CurrentPathStack $path,
    protected readonly ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $logger,
    protected readonly MessengerInterface $messenger,
    protected readonly EntityTypeManager $entityTypeManager,
    protected readonly RequestStack $stack,
    protected readonly StateInterface $stateApi,
  ) {
    $this->config = $this->configFactory->getEditable('patreon.settings');
    $this->logger = $logger->get('patreon');
  }

  /**
   * Helper to return the current scopes.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0. The module
   * no longer supports custom scopes.
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function getScopes(): array {
    return [];
  }

  /**
   * Helper to set the current scopes.
   *
   * @param array $scopes
   *   An array of API scopes.
   *
   * @return string[]
   *   An empty array.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0. The module
   * no longer supports custom scopes.
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function setScopes(array $scopes = []): array {
    return [];
  }

  /**
   * Function to get the supplied token.
   *
   * @return string
   *   Returns the stored token.
   *
   * @throws \Drupal\patreon\PatreonGeneralException
   * @throws \Drupal\patreon\PatreonMissingTokenException
   * @throws \Drupal\patreon\PatreonUnauthorizedException
   */
  public function getToken(): string {
    if ($tokens = $this->getStoredTokens()) {
      if ($tokens->hasExpired()) {
        $tokens = $this->getRefreshedTokens($tokens);
      }

      if ($tokens) {
        return $tokens->getToken();
      }
    }

    throw new PatreonMissingTokenException('An API token has not been set.');
  }

  /**
   * Function to get the supplied refresh token.
   *
   * @return null
   *   Returns a null value.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0. Refresh
   * tokens can be obtained from the AccessTokenInterface returned by
   * $this->>getToken() can provide a refresh token if required.
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function getRefreshToken(): null {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCallback(): Url {
    return Url::fromRoute('patreon.patreon_controller_oauth_callback', [], [
      'absolute' => TRUE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function authoriseAccount(bool $redirect = TRUE): TrustedRedirectResponse|bool|Url|null {
    $return = NULL;

    if ($oauth = $this->getOauth()) {

      // Get the Authorization URL now so the state is set for later.
      $return = Url::fromUri($oauth->getAuthorizationUrl());

      // Get the state store it to the session.
      $session = $this->stack->getCurrentRequest()->getSession();
      $session->set('oauth2state', $oauth->getState());

      if ($redirect) {
        $return = new TrustedRedirectResponse($return->toString());
      }
    }

    return $return;
  }

  /**
   * Deprecated function.
   *
   * @param string $clientId
   *   The client id.
   * @param string $redirectUrl
   *   The redirect URL.
   * @param array $scopes
   *   The scopes.
   * @param string $returnUrl
   *   The return URL.
   *
   * @return bool
   *   Returns FALSE.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0.
   * Authorisation URL is now handled by the Oauth provider returned by
   * $this->>getOauth().
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function getAuthoriseUrl(string $clientId, string $redirectUrl, array $scopes, string $returnUrl = ''): bool {
    return FALSE;
  }

  /**
   * Helper to get a Url Object from a path.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0.
   * The API return URL no longer contains coded values
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function decodeState(string $state): bool {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getOauth(): ?Patreon {
    $return = NULL;

    try {
      $key = $this->config->get('patreon_client_id');
      $secret = $this->config->get('patreon_client_secret');

      if (!$key || !$secret) {
        throw new PatreonMissingTokenException('No client details set.');
      }

      $return = new Patreon([
        'clientId' => $key,
        'clientSecret' => $secret,
        'redirectUri' => $this->getCallback()->toString(),
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error($this->t('Error obtaining new Oauth - :error', [
        ':error' => $e->getMessage(),
      ]));
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function tokensFromCode(string $code): AccessTokenInterface {
    try {
      $oauth = $this->getOauth();
      $tokens = $oauth->getAccessToken('authorization_code', [
        'code' => $code,
      ]);
    }
    catch (IdentityProviderException $e) {
      throw new PatreonUnauthorizedException($e->getMessage());
    }
    catch (\Exception $e) {
      throw new PatreonGeneralException($e->getMessage());
    }

    return $tokens;
  }

  /**
   * {@inheritdoc}
   */
  public function storeTokens(AccessTokenInterface $tokens, ?UserInterface $account = NULL): void {
    $this->stateApi->set('patreon.access_token', Json::encode($tokens));
  }

  /**
   * {@inheritdoc}
   */
  public function getStoredTokens(?UserInterface $account = NULL) :?AccessTokenInterface {
    $return = NULL;

    if ($json = $this->stateApi->get('patreon.access_token')) {
      if ($values = Json::decode($json)) {
        try {
          $return = new AccessToken($values);
        }
        catch (\Exception $e) {
          $this->logger->error($this->t('Error loading stored tokens - :error', [
            ':error' => $e->getMessage(),
          ]));

          return NULL;
        }
      }
    }

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getRefreshedTokens(AccessTokenInterface $tokens): ?AccessTokenInterface {
    if ($oauth = $this->getOauth()) {
      try {
        if ($refreshed = $oauth->getAccessToken('refresh_token', [
          'refresh_token' => $tokens->getRefreshToken(),
        ])) {
          $this->storeTokens($refreshed);

          return $refreshed;
        }
      }
      catch (\Exception $e) {
        $this->logger->error($this->t('Error refreshing tokens - :error', [
          ':error' => $e->getMessage(),
        ]));
        $this->messenger->addError($this->t('Your access tokens could not be refreshed: please reauthorise your account and try again.'));
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueByKey(array $array, array $parents): mixed {
    $nested = new NestedArray();

    return $nested->getValue($array, $parents);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchUser(): ?array {
    return $this->apiFetch('fetch_user');
  }

  /**
   * {@inheritdoc}
   */
  public function fetchCampaign(): ?array {
    return $this->apiFetch('fetch_campaigns');
  }

  /**
   * Helper to fetch Campaign Details.
   *
   * @param string $campaign_id
   *   A Patreon Campaign ID.
   *
   * @return array|null
   *   An array of data from the API or false on error.
   */
  public function fetchCampaignDetails(string $campaign_id): ?array {
    return $this->apiFetch('fetch_campaign_details', [$campaign_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function fetchPagePledges($campaign_id, $page_size, $cursor = NULL): ?array {
    return $this->apiFetch('fetch_page_of_members_from_campaign', [
      $campaign_id,
      $page_size,
      $cursor,
    ]);
  }

  /**
   * Helper to fetch membership details for an id.
   *
   * @param string $member_id
   *   A Patreon membership id.
   *
   * @return array|null
   *   An array of data or NULL on error.
   */
  public function fetchMemberDetails(string $member_id): ?array {
    return $this->apiFetch('fetch_member_details', [$member_id]);
  }

  /**
   * Helper function to query the Patreon API.
   *
   * @param string $function
   *   A valid Patreon API function.
   * @param array $parameters
   *   An array of parameters required for the function call. Defaults to empty.
   *
   * @return null|array
   *   An array of the function callback data, or NULL on error.
   */
  private function apiFetch(string $function, array $parameters = []): ?array {
    $return = NULL;

    try {
      $client = new API($this->getToken());

      if (method_exists($client, $function)) {
        if ($parameters) {

          // Only one of our methods has 3 parameters: all others can be called
          // this way.
          if (count($parameters) < 3) {
            $api_response = $client->{$function}($parameters[0]);
          }
          else {
            [$campaign_id, $page_size, $cursor] = $parameters;
            $api_response = $client->{$function}($campaign_id, $page_size, $cursor);
          }
        }
        else {
          $api_response = $client->{$function}();
        }

        if (!empty($api_response)) {
          if (is_string($api_response)) {
            $api_response = Json::decode($api_response);
          }
          if ($error = $this->getValueByKey($api_response, ['errors', '0'])) {
            if (isset($error['status']) && $error['status'] == '401') {
              throw new PatreonUnauthorizedException('The Patreon API has returned an authorized response.');
            }
            else {
              throw new PatreonGeneralException('Patreon API has returned an unknown response.');
            }
          }
          else {
            $return = $api_response;
          }
        }
        else {
          throw new PatreonGeneralException('Patreon API has returned an unknown response.');
        }
      }
    }
    catch (PatreonMissingTokenException $e) {
      $this->logger->error($this->t('The Patreon API returned the following error: :error', [
        ':error' => $e->getMessage(),
      ]));
      $this->messenger->addError($this->t('A valid API token has not been set. Please visit @link', [
        '@link' => Url::fromRoute('patreon.settings_form')->toString(),
      ]));
    }
    catch (PatreonUnauthorizedException $e) {
      $this->logger->error($this->t('The Patreon API returned the following error: :error', [
        ':error' => $e->getMessage(),
      ]));
      $this->messenger->addError($this->t('Your API token has expired or not been set. Please visit @link', [
        '@link' => Url::fromRoute('patreon.settings_form')->toString(),
      ]));
    }
    catch (PatreonGeneralException $e) {
      $message = $this->t('The Patreon API returned the following error: :error', [
        ':error' => $e->getMessage(),
      ]);
      $this->logger->error($message);
      $this->messenger->addError($message);
    }

    return $return;
  }

  /**
   * Deprecated function.
   *
   * @param string $function
   *   The function call that failed.
   * @param array $parameters
   *   Any parameters for that call.
   *
   * @return bool
   *   The returned API data or FALSE on error.
   *
   * @deprecated in patreon:4.2.0 and is removed from patreon:4.3.0.
   * If retry management is required, it should be handled in your own custom
   * code.
   * @see https://www.drupal.org/project/patreon/issues/3083491
   */
  public function retry(string $function, array $parameters): bool {
    return FALSE;
  }

  /**
   * Helper to get tier data from a membership array.
   *
   * @param array $membership
   *   A return from fetchMembership().
   *
   * @return array
   *   An array of tier data: id => attributes.
   */
  public function getTierData(array $membership): array {
    $return = [];

    if (isset($membership['included'])) {
      foreach ($membership['included'] as $included) {
        if ($included['type'] == 'tier') {

          // The API does not currently return attributes so this will always be
          // empty.
          $return[$included['id']] = $included['attributes'];
        }
      }
    }

    return $return;
  }

  /**
   * Helper to create Drupal roles from Patreon reward types.
   */
  public function createRoles(): array {
    $config_data = [];

    if ($campaigns = $this->fetchCampaign()) {
      $this->storeCampaigns($campaigns);
    }

    $storage = $this->entityTypeManager->getStorage('user_role');
    $roles = $this->getPatreonRoleNames($campaigns);
    $all = array_map(function ($item) {
      return $item->label();
    }, $storage->loadMultiple());

    foreach ($roles as $label => $patreon_id) {
      $id = strtolower(str_replace(' ', '_', $label));
      if (!in_array($label, $all)) {
        $data = [
          'id' => $id,
          'label' => $label,
        ];

        $role = $storage->create($data);
        $role->save();
      }

      $key = ($patreon_id) ?: $id;
      $config_data[$key] = $id;
    }

    $this->stateApi->set('patreon.user_roles', $config_data);

    return $config_data;
  }

  /**
   * Helper to make all campaigns into Drupal roles.
   *
   * @param array|null $campaigns
   *   A return campaign endpoint.
   *
   * @return array
   *   An array of reward titles plus default roles.
   */
  public function getPatreonRoleNames(?array $campaigns = NULL): array {
    $roles = [
      'Patreon User' => NULL,
      'Deleted Patreon User' => NULL,
    ];

    if ($campaigns && $campaign_data = $this->getValueByKey($campaigns, ['data'])) {
      foreach ($campaign_data as $campaign) {
        if ($details = $this->fetchCampaignDetails($campaign['id'])) {
          if (isset($details['included'])) {
            foreach ($details['included'] as $reward) {
              if ($reward['type'] == 'tier') {

                // The Patreon API PHP library does not allow us to fetch fields
                // from included data so we can no longer get the tier label.
                // Roles will have to be given the tier id instead.
                $roles[$reward['id'] . ' Patron'] = $reward['id'];
              }
            }
          }
        }
      }
    }

    return $roles;
  }

  /**
   * Helper to store a list of a users campaigns.
   *
   * @param array $campaigns
   *   An array of data from ->fetchCampaigns or empty to recall.
   */
  public function storeCampaigns(array $campaigns = []): void {
    if (empty($campaigns)) {
      $campaigns = $this->fetchCampaign();
    }

    $store = [];

    if ($campaigns && $campaign_data = $this->getValueByKey($campaigns, ['data'])) {
      foreach ($campaign_data as $campaign) {
        $store[] = $campaign['id'];
      }
    }

    $this->stateApi->set('patreon.campaigns', $store);
  }

  /**
   * Create a link to sign users up to Patreon.
   *
   * @param int $minimum
   *   The minimum pledge amount.
   * @param bool $log_in
   *   Whether to create an account for the user or not.
   *
   * @return \Drupal\Core\Link
   *   A link object.
   */
  public function getSignupLink(int $minimum = 0, bool $log_in = FALSE): Link {

    $redirect_url = ($log_in) ? $this->getCallback()->toString() : $this->stack->getCurrentRequest()->getSchemeAndHttpHost();
    $state = Json::encode([
      'final_page' => $this->path->getPath(),
    ]);

    $url = Url::fromUri('https://www.patreon.com/oauth2/become-patron', [
      'query' => [
        'response_type' => 'code',
        'min_cents' => $minimum,
        'client_id' => $this->config->get('patreon_client_id'),
        'scope' => UrlHelper::encodePath('identity identity[email] identity.memberships campaigns.members'),
        'redirect_uri' => $redirect_url,
        'state' => UrlHelper::encodePath(base64_encode($state)),
      ],
    ]);

    return Link::fromTextAndUrl($this->t('Become a Patron'), $url);
  }

}
