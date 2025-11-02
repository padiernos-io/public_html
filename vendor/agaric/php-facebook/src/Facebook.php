<?php

namespace Agaric\FacebookApi;

use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\Facebook as FacebookAuth;
use League\OAuth2\Client\Token\AccessToken;
use UnexpectedValueException;

class Facebook extends FacebookAuth {

    /**
     * Returns pages that the user, as identified by access token, has a role on.
     *
     * @param string $token
     *   Basic user access token.
     * @param string|NULL $have_task
     *   Task permission that the user must have on each page to include it in the return.
     *   Pass in NULL or any falsy value to not filter the pages.
     * 
     * @return string
     *   Used for making API calls.
     */
    public function getPages(string $token, string $have_task = 'CREATE_CONTENT') {
        $params = [
            'access_token' => $token,
        ];

        $AccessToken = new AccessToken(['access_token' => $token]);
        // $request  = $this->getAccessTokenRequest($params);
        $userId = $this->getResourceOwner($AccessToken)->getId();
        $request = $this->doGetRequest($userId . "/accounts", $params);
        $response = $this->getParsedResponse($request);

        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        $data = $response["data"];
        $pages = [];
        foreach($data as $page) {
            $pages[$page['id']] = $page;
            if ($have_task && !in_array($have_task, $page['tasks'])) {
                unset($pages[$page['id']]);
            }
        } 

        return $pages;
    }

    public function doPostRequest(string $path, array $params) {
        $method  = $this::METHOD_POST;
        $url = static::BASE_GRAPH_URL . $this->graphApiVersion . "/" . $path;

        $options = $this->optionProvider->getAccessTokenOptions($this->getAccessTokenMethod(), $params);

        return $this->getRequest($method, $url, $options);
    }

    public function postToPage(string $page_id, string $page_token, string $message, string $link = '') {
        $client = new Client(['base_uri' => static::BASE_GRAPH_URL]);
        $body = ['message' => $message, 'access_token' => $page_token];
        if ($link) {
            $body['link'] = $link;
        }
        $response = $client->post($this->graphApiVersion . '/' . $page_id . '/feed', [
            'form_params' => $body,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);
        return $response;
    }

    public function doGetRequest(string $path, array $params) {
        $method  = $this::METHOD_GET;
        $url = static::BASE_GRAPH_URL . $this->graphApiVersion . "/" . $path;

        return $this->getRequest($method, $url . '?' . http_build_query($params));
    }

    public function getLongLivedPageAccessToken(string $token, string $pageId): string {
        if (!$this->longLivedToken) {
            $AccessToken = $this->getLongLivedAccessToken($token);
            $this->longLivedToken = $AccessToken->getToken();
        }
    
        $params = [
            'access_token' => $this->longLivedToken,
            'fields' => 'access_token',
        ];

        $request = $this->doGetRequest($pageId, $params);
        $response = $this->getParsedResponse($request);
        return $response['access_token'];
    }

    public function getAllLongLivedPageAccessTokens(string $token) {
        $pages = $this->getPages($token);
        foreach ($pages as $page_id => &$page) {
            $long_access_token = $this->getLongLivedPageAccessToken($token, $page_id);
            $page['long_access_token'] = $long_access_token;
        }
        return $pages;
    }
}