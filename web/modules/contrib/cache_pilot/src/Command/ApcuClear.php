<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Command;

use Drupal\cache_pilot\Cache\ApcuCache;
use Drush\Style\DrushStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides a command to reset APCu caches.
 */
#[AsCommand(
  name: 'cache-pilot:apcu:clear',
  description: 'Clears the APCu caches.'
)]
final class ApcuClear extends Command {

  public function __construct(
    private ApcuCache $cache,
  ) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $is_success = $this->cache->clear();
    $io = new DrushStyle($input, $output);

    if ($is_success) {
      $io->success('Done!');
      return self::SUCCESS;
    }
    else {
      $io->getErrorStyle()->error('Failed to clear cache, check logs for detailed information.');
      return self::FAILURE;
    }
  }

}
