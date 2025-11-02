<?php

namespace Drupal\backstop_generator\Entity;

use Drupal\backstop_generator\BackstopViewportInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the backstop viewport entity type.
 *
 * @ConfigEntityType(
 *   id = "backstop_viewport",
 *   label = @Translation("Backstop Viewport"),
 *   label_collection = @Translation("Backstop Viewports"),
 *   label_singular = @Translation("backstop viewport"),
 *   label_plural = @Translation("backstop viewports"),
 *   label_count = @PluralTranslation(
 *     singular = "@count backstop viewport",
 *     plural = "@count backstop viewports",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\backstop_generator\BackstopViewportListBuilder",
 *     "form" = {
 *       "add" = "Drupal\backstop_generator\Form\BackstopViewportForm",
 *       "edit" = "Drupal\backstop_generator\Form\BackstopViewportForm",
 *       "delete" = "Drupal\backstop_generator\Form\BackstopViewportDeleteForm"
 *     }
 *   },
 *   config_prefix = "viewport",
 *   admin_permission = "administer backstop_generator",
 *   links = {
 *     "collection" = "/admin/config/development/backstop-generator/viewports",
 *     "add-form" = "/admin/config/development/backstop-generator/viewport/add",
 *     "edit-form" = "/admin/config/development/backstop-generator/viewport/{backstop_viewport}",
 *     "delete-form" = "/admin/config/development/backstop-generator/viewport/{backstop_viewport}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "height",
 *     "width"
 *   }
 * )
 */
class BackstopViewport extends ConfigEntityBase implements BackstopViewportInterface {

  /**
   * The backstop viewport ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The backstop viewport label.
   *
   * @var string
   */
  protected $label;

  /**
   * The backstop viewport status.
   *
   * @var bool
   */
  protected $status;

  /**
   * The backstop_viewport description.
   *
   * @var string
   */
  protected $description;

  /**
   * The height in pixels of the viewport.
   *
   * @var int
   */
  protected $height;

  /**
   * The width in pixels of the viewport.
   *
   * @var int
   */
  protected $width;

  /**
   * Determine if the key belongs in the JSON file.
   *
   * @return array
   *   The key value pairs that belong in the JSON file.
   */
  public function getJsonKeyValues(): array {
    $non_json_keys = [
      'id', 'uuid', 'status', 'dependencies', 'langcode', 'description',
    ];

    $json_keys = array_filter(
      $this->toArray(),
      function ($key) use ($non_json_keys) {
        return !in_array($key, $non_json_keys);
      },
      ARRAY_FILTER_USE_KEY,
    );
    return array_filter($json_keys);
  }

}
