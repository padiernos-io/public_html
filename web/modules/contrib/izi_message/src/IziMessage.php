<?php

namespace Drupal\izi_message;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides pre_render for a messages element.
 *
 * @see Drupal\Core\Render\Element\StatusMessages
 */
class IziMessage extends RenderElement {

  /**
   * {@inheritdoc}
   *
   * Generate the placeholder in a #pre_render callback.
   */
  public function getInfo() {
    return [
      '#display' => NULL,
      '#include_fallback' => FALSE,
    ];
  }

  /**
   * Callback to generate a placeholder.
   *
   * @param array $element
   *   A renderable array.
   *
   * @return array
   *   The updated renderable array containing the placeholder.
   */
  public static function generatePlaceholder(array $element) {
    $build = [
      '#lazy_builder' => [
        __CLASS__ . '::renderMessages',
        [$element['#display']],
      ],
      '#create_placeholder' => TRUE,
    ];

    $build = \Drupal::service('render_placeholder_generator')->createPlaceholder($build);

    if ($element['#include_fallback']) {
      return [
        'messages'  => $build,
        'fallback'  => [
          '#markup'   => '<div data-drupal-messages-fallback class="hidden"></div>',
        ],
      ];
    }
    return $build;
  }

  /**
   * Replaces placeholder with messages.
   *
   * @param string|null $type
   *   Limit the messages returned by type. Defaults to NULL, meaning all types.
   *   Passed on to \Drupal\Core\Messenger\Messenger::deleteByType(). These
   *   values are supported:
   *   - NULL
   *   - 'status'
   *   - 'warning'
   *   - 'error'.
   *
   * @return array
   *   A renderable array containing the messages.
   *
   * @see \Drupal\Core\Messenger\Messenger::deleteByType()
   */
  public static function renderMessages($type = NULL) {
    $render = [];
    if (isset($type)) {
      $messages = [
        $type => \Drupal::messenger()->deleteByType($type),
      ];
    }
    else {
      $messages = \Drupal::messenger()->deleteAll();
    }

    if ($messages) {
      // Render the messages.
      $render = [
        '#theme'        => 'izi_message',
        '#message_list' => $messages,
        '#attached'     => [
          'library'       => ['izi_message/izi_message'],
        ],
        '#status_headings' => [
          'status'  => t('Status message'),
          'error'   => t('Error message'),
          'warning' => t('Warning message'),
        ],
      ];
    }
    return $render;
  }

}
