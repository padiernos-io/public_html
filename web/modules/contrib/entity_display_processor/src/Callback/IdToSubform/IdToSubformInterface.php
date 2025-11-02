<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Callback\IdToSubform;

use Drupal\Core\Form\SubformStateInterface;

/**
 * Callback to build a subform array given an id.
 *
 * @internal
 *   This component may be renamed and/or moved to a different module any time.
 */
interface IdToSubformInterface {

  /**
   * The actual callback method.
   *
   * @param int|string $id
   *   An id to specify which subform to build.
   * @param array $settings
   *   Values for the subform.
   * @param \Drupal\Core\Form\SubformStateInterface $form_state
   *   The subform state.
   *
   * @return array
   *   The subform element.
   */
  public function __invoke(int|string $id, array $settings, SubformStateInterface $form_state): array;

}
