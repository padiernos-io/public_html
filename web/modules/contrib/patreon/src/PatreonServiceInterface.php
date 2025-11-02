<?php

namespace Drupal\patreon;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\user\UserInterface;
use HansPeterOrding\OAuth2\Client\Provider\Patreon;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * Interface for the Patreon Service.
 *
 * @package Drupal\patreon
 */
interface PatreonServiceInterface {

  const string PATREON_REGISTER_OAUTH_URL = 'https://www.patreon.com/platform/documentation/clients';

  /**
   * Returns OAuth2 provider ore NULL, on error.
   *
   * @return \HansPeterOrding\OAuth2\Client\Provider\Patreon|null
   *   The provider or NULL on error.
   */
  public function getOauth(): ?Patreon;

  /**
   * Helper to return the valid absolute Oauth Callback URL.
   *
   * @return \Drupal\Core\Url
   *   The absolute URL of the Oauth Callback route,
   */
  public function getCallback(): Url;

  /**
   * Authorise a new account on the API.
   *
   * @param bool $redirect
   *   Whether to redirect the user directly to the API URL.
   *
   * @return bool|\Drupal\Core\Routing\TrustedRedirectResponse|\Drupal\Core\Url|null
   *   A redirect response or URL
   */
  public function authoriseAccount(bool $redirect = TRUE): trustedRedirectResponse|bool|Url|null;

  /**
   * Converts an API return string into tokens.
   *
   * @param string $code
   *   A string returned by the API.
   *
   * @return \League\OAuth2\Client\Token\AccessTokenInterface
   *   The League Oauth access token
   *
   * @throws \Drupal\patreon\PatreonGeneralException
   * @throws \Drupal\patreon\PatreonUnauthorizedException
   */
  public function tokensFromCode(string $code): AccessTokenInterface;

  /**
   * Store the tokens provided by the Patreon Oauth API.
   *
   * @param \League\OAuth2\Client\Token\AccessTokenInterface $tokens
   *   The Access Tokens returned by the League Oauth2 Library.
   * @param \Drupal\user\UserInterface|null $account
   *   The account of the user storing the tokens. Optional.
   */
  public function storeTokens(AccessTokenInterface $tokens, ?UserInterface $account = NULL);

  /**
   * Load the tokens stored by $this->storeTokens().
   *
   * @param \Drupal\user\UserInterface|null $account
   *   The account of the user requesting the tokens. Optional.
   *
   * @return \League\OAuth2\Client\Token\AccessTokenInterface|null
   *   An array of tokens.
   */
  public function getStoredTokens(?UserInterface $account = NULL): ?AccessTokenInterface;

  /**
   * Helper to get refreshed tokens from the Patreon API.
   *
   * @param \League\OAuth2\Client\Token\AccessTokenInterface $tokens
   *   The current PatreonAPI Oauth2 tokens.
   *
   * @return \League\OAuth2\Client\Token\AccessTokenInterface|null
   *   Returns a refreshed token or NULL on error.
   *
   * @throws \Drupal\patreon\PatreonGeneralException
   * @throws \Drupal\patreon\PatreonUnauthorizedException
   */
  public function getRefreshedTokens(AccessTokenInterface $tokens): ?AccessTokenInterface;

  /**
   * Helper to get a specified value from a Patreon API return.
   *
   * @param array $array
   *   An array of data.
   * @param array $parents
   *   An array of parent keys of the value, starting with the outermost key.
   *
   * @return mixed|null
   *   The value, or NULL on error.
   *
   * @see NestedArray
   */
  public function getValueByKey(array $array, array $parents): mixed;

  /**
   * Helper to return user data from the Patreon API.
   *
   * @return null|array
   *   An array of data from the Patreon API, or NULL on error.
   */
  public function fetchUser(): ?array;

  /**
   * Helper to return campaign data from the Patreon API.
   *
   * @return null|array
   *   An array of data from the Patreon API, or NULL on error.
   */
  public function fetchCampaign(): ?array;

  /**
   * Fetch a paged list of pledge data from the Patreon API.
   *
   * @param int $campaign_id
   *   A valid Patreon campaign id.
   * @param int $page_size
   *   The number of items per page.
   * @param null|string $cursor
   *   A cursor character.
   *
   * @return null|array
   *   An array of data from the Patreon API or NULL on error.
   */
  public function fetchPagePledges(int $campaign_id, int $page_size, null|string $cursor = NULL): ?array;

}
