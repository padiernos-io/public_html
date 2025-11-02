<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Cache;

use Drupal\Component\Serialization\Json;
use Drupal\cache_pilot\Client\Client;
use Drupal\cache_pilot\Contract\CacheInterface;
use Drupal\cache_pilot\Data\ClientCommand;

/**
 * Provides an APCu cache integration.
 */
final readonly class ApcuCache implements CacheInterface {

  public function __construct(
    private Client $client,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function clear(): bool {
    return $this->client->sendCommand(ClientCommand::ApcuClear)->getBody() === 'Ok';
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled(): bool {
    return $this->client->sendCommand(ClientCommand::ApcuStatus)->getBody() === 'Ok';
  }

  /**
   * {@inheritdoc}
   */
  public function statistics(): array {
    $statistics = Json::decode($this->client->sendCommand(ClientCommand::ApcuStatistic)->getBody());
    return is_array($statistics) ? $statistics : [];
  }

}
