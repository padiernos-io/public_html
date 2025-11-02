# Cookie Information

This module is created to make it easier to integrate your Drupal installation with the cookie platform Cookie information.

The module includes a basic integration to the service and some additional features like IAB and Google Consent Mode support.

## Table of contents

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Troubleshooting
 * Sponsors
 * Maintainers

## Requirements

* One or more cookieinformation.com subscriptions.

## Installation

* Install this module as any other Drupal module, see the documentation on
[Drupal.org](https://www.drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules).

## Configuration

* Go to /admin/config/system/cookie-information.
* Enable consent widget and configure visibility if required.

### Google consent mode

The Cookie information Drupal module has build in support for [Google Consent Mode](https://support.google.com/analytics/answer/9976101?hl=en). This adds the requires tags to allow collecting anonymous statistics from the visitors.

The latest version of the module also supports v2. There is a [upgrade documentation](https://support.cookieinformation.com/en/articles/8707953-how-to-update-to-google-consent-mode-v2) for the needed changes. All the other changes are handled by the module but the step that needs changing the template to support Google Consent Mode v2 needs to be done in the [platform settings](http://go.cookieinformation.com/).

Please be aware that the module will implement the Advanced Mode of GCM v2.

### Iframes

The module has support for third-party iframe blocking like described in the Cookie information [documentation](https://support.cookieinformation.com/en/articles/5451699-pixels-iframe-and-serverside-php). If the iframe blocking configuration is enabled, the module will block the iframes in client side with Javascript. It changes the iframe `src` as `data-consent-src`and also adds the `data-consent-category` attribute which tells Cookie information which category cookie should be accepted for the element to be displayed.

The configuration includes a setting that allows the site administrator to select the default category used when iframes are blocked. Default category is functional.

The module will display a placeholder for the user that allows them to open the Cookie information banner and allow the correct cookie category for the iframes to be loaded. This functionality relies on the build in functionality of Cookie information to [show and hide the placeholder](https://support.cookieinformation.com/en/articles/4418529-provide-placeholder-for-blocked-page-elements-youtube-vimeo-etc) before the blocked element is loaded.

The client side blocking is disabled if the iframe already includes the necessary attributes. In this case it is expected that there is a custom solution to give necessary information for the end user to accept a certain cookie category.

A similar custom solution can be used to block for example [third party scripts](https://support.cookieinformation.com/en/articles/5444629-third-party-cookie-blocking).

## Troubleshooting

* Submit an issue at
  https://www.drupal.org/project/issues/cookieinformation.

## Contributing

DDEV configuration is available for local development. To get started, install [DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/) and follow the instructions from the DDEV add-on [ddev-drupal-contrib](https://github.com/ddev/ddev-drupal-contrib) to get local development started.

## Sponsors

* [FFW](https://ffwagency.com)
* [Exove](https://www.exove.com)

## Maintainers

Current maintainers:
* [Jens Beltofte](https://drupal.org/u/beltofte)
* [Heikki Ylipaavalniemi](https://drupal.org/u/HeikkiY)
* [Kalle Kipinä](https://drupal.org/u/kekkis)
