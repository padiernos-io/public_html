<?php

namespace Drupal\reassign_user_content;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Media batch service.
 */
class MediaBatchService implements ContainerInjectionInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * The object renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * MediaBatchService constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The object renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Helper function to reassign array of medias.
   *
   * @param array $medias
   *   Media array.
   * @param mixed $uuid
   *   User uuid.
   */
  public function reassignUserMedia(array $medias, $uuid): void {
    if (empty($medias)) {
      return;
    }

    if (count($medias) > 10) {
      $batch_builder = (new BatchBuilder())
        ->addOperation([$this, 'mediaBatchProcess'], [$medias, $uuid])
        ->setFinishCallback([$this, 'mediaBatchFinished'])
        ->setTitle($this->t('Processing'))
        ->setErrorMessage($this->t('The update has encountered an error.'))
        // We use a single multi-pass operation, so the default
        // 'Remaining x of y operations' message will be confusing here.
        ->setProgressMessage('');
      batch_set($batch_builder->toArray());
    }
    else {
      $this->reassignChunkMedias($medias, $uuid);
    }
  }

  /**
   * Reassign chunk of medias.
   *
   * @param array $medias
   *   Array of media.
   * @param mixed $uuid
   *   User uuid.
   */
  public function reassignChunkMedias(array $medias, $uuid): void {
    if (empty($medias)) {
      return;
    }

    foreach ($medias as $media) {
      $lang_codes = array_keys($media->getTranslationLanguages());
      // For efficiency manually save the original media before applying any
      // changes.
      $media->original = clone $media;
      foreach ($lang_codes as $lang_code) {
        $media_translated = $media->getTranslation($lang_code);
        $media_translated->setOwnerId($uuid);
      }
      $media->save();
    }
  }

  /**
   * Media batch process callback.
   *
   * @param array $medias
   *   Array of media.
   * @param mixed $uuid
   *   User uuid.
   * @param mixed $context
   *   Batch context.
   */
  public function mediaBatchProcess(array $medias, $uuid, &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($medias);
      $context['sandbox']['medias'] = $medias;
    }

    // Process medias by groups of 5.
    $count = min(5, count($context['sandbox']['medias']));
    for ($i = 1; $i <= $count; $i++) {
      $media = array_shift($context['sandbox']['medias']);
      // For each media, set the uid to new user, and save it.
      $this->reassignChunkMedias([$media], $uuid);
      // Store result for post-processing in the finished callback.
      $context['results'][] = Link::fromTextAndUrl($media->label(), $media->toUrl())
        ->toString();
      // Update our progress information.
      $context['sandbox']['progress']++;
    }

    // Inform the batch engine that we are not finished,
    // and provide an estimation of the completion level we reached.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Media batch finished callback.
   *
   * @param mixed $success
   *   Success.
   * @param mixed $results
   *   Results.
   * @param mixed $operations
   *   Operations.
   *
   * @throws \Exception
   */
  public function mediaBatchFinished($success, $results, $operations): void {
    if ($success) {
      $this->messenger()
        ->addStatus($this->t('The medias update has been performed.'));
    }
    else {
      $this->messenger()
        ->addError($this->t('An error occurred and processing did not complete.'));
      $message = $this->stringTranslation->formatPlural(count($results), '1 item successfully processed:', '@count items successfully processed:');
      $item_list = [
        '#theme' => 'item_list',
        '#items' => $results,
      ];
      $message .= $this->renderer->render($item_list);
      $this->messenger()->addStatus($message);
    }
  }

}
