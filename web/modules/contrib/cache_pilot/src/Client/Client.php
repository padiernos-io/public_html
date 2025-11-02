<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Client;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\cache_pilot\Connection\SocketConnectionBuilder;
use Drupal\cache_pilot\Data\ClientCommand;
use Drupal\cache_pilot\Exception\MissingConnectionTypeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use hollodotme\FastCGI\Client as FastCgiClient;
use hollodotme\FastCGI\Interfaces\ProvidesResponseData;
use hollodotme\FastCGI\Requests\PostRequest;
use hollodotme\FastCGI\Responses\Response;

/**
 * Provides a FastCGI client.
 */
final readonly class Client {

  public function __construct(
    #[Autowire(service: 'logger.channel.cache_pilot')]
    private LoggerChannelInterface $logger,
    private SocketConnectionBuilder $connection,
    private ModuleExtensionList $moduleExtensionList,
  ) {}

  /**
   * Checks if the connection to the FastCGI is established.
   *
   * @return bool
   *   TRUE if the connection is established, FALSE otherwise.
   */
  public function isConnected(): bool {
    return $this->sendCommand(ClientCommand::Echo)->getBody() === 'Ok';
  }

  /**
   * Sends the command to the FastCGI server.
   *
   * @param \Drupal\cache_pilot\Data\ClientCommand $command
   *   The command to send.
   *
   * @return \hollodotme\FastCGI\Interfaces\ProvidesResponseData
   *   The response from the FastCGI server.
   */
  public function sendCommand(ClientCommand $command): ProvidesResponseData {
    return $this->sendSocketRequest($command->value);
  }

  /**
   * Sends the command to the FastCGI server via socket.
   *
   * @param string $command
   *   The command to send.
   *
   * @return \hollodotme\FastCGI\Interfaces\ProvidesResponseData
   *   The response from the FastCGI server.
   */
  protected function sendSocketRequest(string $command): ProvidesResponseData {
    try {
      $connection = $this->connection->build();
    }
    catch (MissingConnectionTypeException) {
      return new Response('', 'Missing connection type', 0);
    }

    $module_path = $this->moduleExtensionList->getPath('cache_pilot');
    $script_path = \DRUPAL_ROOT . '/' . $module_path . '/cache-pilot.php';
    $client = new FastCgiClient();
    $request = new PostRequest(
      scriptFilename: $script_path,
      content: http_build_query(['command' => $command]),
    );
    $request->setCustomVar('cache_pilot', '1');

    try {
      $response = $client->sendRequest($connection, $request);
    }
    catch (\Throwable $e) {
      $this->logger->error(
        message: 'The request failed: @message',
        context: ['@message' => $e->getMessage()],
      );
      return new Response('', 'Request failed', 0);
    }

    return $response;
  }

}
