<?php

declare(strict_types=1);

namespace HansPeterOrding\OAuth2\Client\Provider;

use HansPeterOrding\OAuth2\Client\Provider\Exception\PatreonIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Patreon extends AbstractProvider
{
	use BearerAuthorizationTrait;

	public string $domain = 'https://www.patreon.com/';
	public string $apiDomain = 'https://api.patreon.com/';

	public function getBaseAuthorizationUrl()
	{
		return $this->domain . 'oauth2/authorize';
	}

	public function getBaseAccessTokenUrl(array $params)
	{
		return $this->domain . 'api/oauth2/token';
	}

	public function getResourceOwnerDetailsUrl(AccessToken $token)
	{
		return $this->domain . 'api/oauth2/v2/identity?include=memberships.currently_entitled_tiers,memberships.campaign&fields[user]=email,first_name,full_name,image_url,last_name,thumb_url,url,vanity,is_email_verified&fields[member]=currently_entitled_amount_cents,lifetime_support_cents,campaign_lifetime_support_cents,last_charge_status,patron_status,last_charge_date,pledge_relationship_start';
	}

	protected function getDefaultScopes()
	{
		return ['identity.memberships identity identity[email] campaigns'];
	}

	protected function checkResponse(ResponseInterface $response, $data)
	{
		if ($response->getStatusCode() >= 400) {
			throw PatreonIdentityProviderException::clientException($response, $data);
		} elseif (isset($data['error'])) {
			throw PatreonIdentityProviderException::oauthException($response, $data);
		}
	}

	protected function createResourceOwner(array $response, AccessToken $token)
	{
		$user = new PatreonResourceOwner($response);

		return $user->setDomain($this->domain);
	}
}
