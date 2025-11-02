<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Connection;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\cache_pilot\Exception\InvalidConfigurationException;
use Drupal\cache_pilot\Exception\MissingConnectionTypeException;
use hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection;
use hollodotme\FastCGI\SocketConnections\NetworkSocket;
use hollodotme\FastCGI\SocketConnections\UnixDomainSocket;

/**
 * Prepares socket connection.
 */
final readonly class SocketConnectionBuilder {

  public function __construct(
    private ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Prepares connection to socket.
   *
   * @return \hollodotme\FastCGI\Interfaces\ConfiguresSocketConnection
   *   The socket connection configuration.
   *
   * @throws \Drupal\cache_pilot\Exception\InvalidConfigurationException
   * @throws \Drupal\cache_pilot\Exception\MissingConnectionTypeException
   */
  public function build(): ConfiguresSocketConnection {
    $connection_dsn = $this->configFactory->get('cache_pilot.settings')->get('connection_dsn');
    if (!$connection_dsn || !\is_string($connection_dsn)) {
      throw new MissingConnectionTypeException();
    }

    $connection_config = ConnectionConfig::fromDsn($connection_dsn);
    if ($connection_config->type === 'tcp') {
      \assert(\is_string($connection_config->host) && \is_numeric($connection_config->port));
      return new NetworkSocket($connection_config->host, $connection_config->port);
    }
    if ($connection_config->type === 'unix') {
      \assert(\is_string($connection_config->socketPath));
      return new UnixDomainSocket($connection_config->socketPath);
    }

    throw new InvalidConfigurationException("Malformed DSN configuration: {$connection_dsn}");
  }

}
