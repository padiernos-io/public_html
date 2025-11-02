<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Marks an EntityDisplayProcessor plugin.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EntityDisplayProcessor extends Plugin {

  /**
   * Constructor.
   *
   * @param string $id
   *   The plugin id.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $label
   *   The plugin label.
   * @param class-string|null $deriver
   *   (optional) The plugin deriver class.
   */
  public function __construct(
    string $id,
    public readonly ?TranslatableMarkup $label = NULL,
    ?string $deriver = NULL,
  ) {
    parent::__construct($id, $deriver);
  }

}
