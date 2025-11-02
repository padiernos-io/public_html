<?php

declare(strict_types=1);

namespace HansPeterOrding\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class PatreonResourceOwner implements ResourceOwnerInterface
{
	use ArrayAccessorTrait;

	protected string $domain;

	protected array $response;

	public function __construct(array $response = [])
	{
		$this->response = $response;
	}

	public function getId()
	{
		return $this->getValueByKey($this->response['data'], 'id');
	}

	public function setDomain(string $domain): self
	{
		$this->domain = $domain;

		return $this;
	}

	public function toArray()
	{
		return $this->response;
	}

	/**
	 * @param string[] $allowedTierIds
	 */
	public function getPatreonTier(array $allowedTierIds): ?string
	{
		$included = $this->response['included'] ?: null;

		if ($included) {
			foreach ($included as $key => $inc) {
				if ($inc['type'] === 'tier' && in_array($inc['id'], $allowedTierIds)) {
					return $inc['id'];
				}
			}
		}

		return null;
	}

	public function getUserName(): ?string
	{
		if (array_key_exists('full_name', $this->response['data']['attributes'])) {
			return $this->response['data']['attributes']['full_name'];
		}

		return null;
	}

	public function getFirstName(): ?string
	{
		if (array_key_exists('first_name', $this->response['data']['attributes'])) {
			return $this->response['data']['attributes']['first_name'];
		}

		return null;
	}

	public function getLastName(): ?string
	{
		if (array_key_exists('last_name', $this->response['data']['attributes'])) {
			return $this->response['data']['attributes']['last_name'];
		}

		return null;
	}

	public function getEmail(): ?string
	{
		if (array_key_exists('email', $this->response['data']['attributes'])) {
			return $this->response['data']['attributes']['email'];
		}

		return null;
	}
}
