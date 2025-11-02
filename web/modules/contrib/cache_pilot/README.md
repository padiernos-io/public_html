# Cache Pilot

The module provides easy-to-use configuration, service, and command-line tools
for clearing the APCu and/or Zend OPcache caches. The cache is cleared through
FastCGI using TCP or Unix Sockets. This is necessary when clearing the cache
from the command line during deployment because it uses a dedicated instance of
PHP-FPM.

## Table of contents

- Requirements
- Installation
- Configuration
- Usage
- FAQ

## Requirements

- Drupal 10.3+
- PHP 8.3+
- (optional) APCu
- (optional) Zend OPcache

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see [Installing Drupal Modules][1].

## Configuration

To configure a module, go to: **Configuration** > **Performance** > **Cache
Pilot Settings**.

There are several settings available:

* **Connection DSN:** This setting defines how the module connects to FastCGI.
  You have three options to choose from:
  * **Empty value:** This option disables the connection. It is useful for
    development environments where you don't need this functionality.
  * **TCP:** Connects using the '[host]:[port]' information, for example:
    `tcp://127.0.0.1:9000` or `tcp://php:9000`.
  * **Unix domain socket:** Connects using a unix socket to which you provide a
    path, for example: `unix:///var/run/php/php-fpm.sock`.

## Usage

The main purpose of this module is to clear the Zend OPcache and/or APCu caches
during deployment using the command-line interface (CLI). To make it easier to
use, the module provides two commands:

* `drush cache-pilot:apcu:clear`: Clears APCu caches.
* `drush cache-pilot:opcache:clear`: Clears Zend OPcache caches.

You can clear these caches from the user interface (UI) or directly from the
code using a dedicated service for that purpose.

## FAQ

### How to disable it for a specific environment?

To disable the functionality of the module in a particular environment, it is
recommended to use settings.php to override the configuration:

```php
$config['cache_pilot.settings']['connection_dsn'] = NULL;
```

[1]: https://www.drupal.org/docs/extending-drupal/installing-drupal-modules
