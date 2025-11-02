<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Connection;

/**
 * Immutable configuration container for Fast-CGI connections.
 */
final readonly class ConnectionConfig {

  /**
   * Constructs a new ConnectionConfig instance.
   *
   * @param non-empty-string $type
   *   The connection type. Supported values:
   *   - 'tcp': TCP/IP connection (requires $host and $port)
   *   - 'unix': Unix Domain Socket connection (requires $socketPath)
   * @param non-empty-string|null $socketPath
   *   The filesystem path to the Unix socket. Must be:
   *   - Non-empty string for 'unix' type
   *   - NULL for 'tcp' type.
   * @param string|null $host
   *   The TCP hostname/IP address. Must be:
   *   - Non-empty string for 'tcp' type
   *   - NULL for 'unix' type.
   * @param int|null $port
   *   The TCP port number. Must be:
   *   - Valid port (1-65535) for 'tcp' type
   *   - NULL for 'unix' type.
   */
  public function __construct(
    public string $type,
    public ?string $socketPath,
    public ?string $host,
    public ?int $port,
  ) {
    $this->validate();
  }

  /**
   * Creates a ConnectionConfig instance from a DSN string.
   *
   * Supported DSN formats:
   * - TCP: "tcp://host:port"
   * - Unix: "unix:///path/to/socket.sock"
   *
   * @param string $dsn
   *   Data Source Name string.
   *
   * @return self
   *   Configured connection instance.
   *
   * @throws \InvalidArgumentException
   *   On invalid DSN format or parsing errors.
   */
  public static function fromDsn(string $dsn): self {
    [$type, $reminder] = \array_pad(\explode('://', $dsn), 2, NULL);

    if ($type === 'tcp') {
      $connection = parse_url($reminder);
      if (!$connection || !isset($connection['host']) || !isset($connection['port'])) {
        throw new \InvalidArgumentException("Malformed DSN: {$dsn}.");
      }

      return new self($type, NULL, $connection['host'], $connection['port']);
    }
    elseif ($type === 'unix') {
      return new self($type, $reminder, NULL, NULL);
    }

    throw new \InvalidArgumentException("Unsupported DSN type: {$type} (dsn: {$dsn})");
  }

  /**
   * Performs comprehensive validation of all connection parameters.
   *
   * @throws \InvalidArgumentException
   *   When any validation rule is violated.
   */
  private function validate(): void {
    match ($this->type) {
      'tcp' => $this->validateTcpParams(),
      'unix' => $this->validateUnixParams(),
      default => throw new \InvalidArgumentException("Invalid connection type '{$this->type}'. Allowed values: tcp, unix"),
    };
  }

  /**
   * Validates TCP-specific connection parameters.
   *
   * @throws \InvalidArgumentException
   *   When TCP parameters are invalid or inconsistent.
   */
  private function validateTcpParams(): void {
    $errors = [];

    if ($this->host === NULL || trim($this->host) === '') {
      $errors[] = 'Host is required for TCP connections';
    }

    if ($this->port === NULL) {
      $errors[] = 'Port is required for TCP connections';
    }
    elseif ($this->port < 1 || $this->port > 65535) {
      $errors[] = "Invalid port '{$this->port}'. Must be 1-65535";
    }

    if ($this->socketPath !== NULL) {
      $errors[] = 'Socket path must be null for TCP connections';
    }

    $this->throwIfErrors($errors);
  }

  /**
   * Validates Unix socket-specific connection parameters.
   *
   * @throws \InvalidArgumentException
   *   When Unix parameters are invalid or inconsistent.
   */
  private function validateUnixParams(): void {
    $errors = [];

    if ($this->socketPath === NULL || trim($this->socketPath) === '') {
      $errors[] = 'Socket path is required for Unix connections';
    }

    if ($this->host !== NULL || $this->port !== NULL) {
      $errors[] = 'Host and port must be null for Unix connections';
    }

    $this->throwIfErrors($errors);
  }

  /**
   * Throws aggregated validation errors as a single exception.
   *
   * @param list<string> $errors
   *   List of validation error messages.
   *
   * @throws \InvalidArgumentException
   */
  private function throwIfErrors(array $errors): void {
    if (count($errors) === 0) {
      return;
    }

    throw new \InvalidArgumentException((implode('. ', $errors)));
  }

}
