## SUMMARY

The Patreon module implements [the patreon.php library](https://github.com/Patreon/patreon-php) to connect a Drupal site
with the Patreon API. This allows other modules to access data from an authorised [Patreon](https://www.patreon.com)
account, either the creator or their patrons.

## REQUIREMENTS

A client key and secret must be obtained by registering at [https://www.patreon.com/platform/documentation/clients](https://www.patreon.com/platform/documentation/clients).
The module's endpoint <code>/patreon/oauth</code> must be registered as an allowed redirect destination in the client
application.

The Patreon API library has a dependency on composer, which means that this module must also be installed using
composer. You can find instructions for managing a Drupal site using composer at https://www.drupal.org/node/2718229.

The authentication between Drupal and the Patreon API is handled using the [oauth2-patreon](https://github.com/HansPeterOrding/oauth2-patreon)
library, which implements a [League/oauth2-client](https://oauth2-client.thephpleague.com/).

## INSTALLATION

The Patreon API library has a dependency on composer, which means that this module must also be installed using
composer. You can find instructions for managing a Drupal site using composer at [https://www.drupal.org/node/2718229](https://www.drupal.org/node/2718229).

## CONFIGURATION

A valid client id and secret key must be added to the form at <code>/admin/config/services/patreon/settings</code>, and
access to the creator account when prompted.

## CUSTOMIZATION

This module provides a Service linking Drupal and the Patreon API, allowing
other modules to provide functionality reliant on the API. Once configured,
the Service provides three main methods to obtain data from Patreon:

```
* ->fetchUser()
* ->fetchCampaign()
* ->fetchPagePledges()
```

Each returns an array of data obtained from the Patreon API. The Service contains a <code>* ->getValueByKey()</code>
method to assist with drilling down into the data.

The functions correspond to the documented functions provided by the patreon.php library, and each uses the default user
access token stored in the module's settings when the creator authorises their account. If you require a way of using
alternative tokens, you can use the Patreon User submodule's patreon_user.api service: this extends the base library and
allows alternative AccessTokenInterface tokens to be passed to the API calls:

```php

  $service = \Drupal::service('patreon_user.api');
  $token = new AccessToken(['access_token' => YOUR_ACCESS_TOKEN]);
  $service->setToken($token);

  // Use as required, for example:
  $results = $service->fetchUser();

```

Custom modules can implement their own authorisation processes by using the Service's
<code>->authoriseAccount()</code> and <code>->getStoredTokens()</code> methods, and overriding the <code>->getCallback()</code>
method.

## CHANGES FROM 4.1 VERSION
The main change from the 4.1 version of this module is the use of a [League/oauth2-client](https://oauth2-client.thephpleague.com/):
this means that some of the underlying methods have been deprecated, and the <code>PatreonService::getOauth()</code> method
now returns an AccessTokenInterface object instead of an array of token details. The code of the module has been updated
to expect this, but if you are using the service in custom code you may need to make alterations.

Deprecated methods have been marked and will be removed from future versions of the module.

## TROUBLESHOOTING

If you are implementing a custom authorisation process and using
<code>->authoriseAccount()</code> fails to redirect the user to Patreon's
authorisation page, check that you have added your callback URL to the
Service by overriding <code>->getCallback()</code>.

## CONTACT

Current maintainer:

* [Dale Smith (MrDaleSmith)](https://drupal.org/user/2612656)
