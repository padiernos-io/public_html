<?php

namespace Drupal\menu_link_content_visibility\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringLongItem;

/**
 * MenuLinkContentVisibility data type.
 *
 * @FieldType(
 *   label = @Translation("Menu link visibility"),
 *   id = "menu_link_content_visibility",
 *   default_widget = "menu_link_content_visibility",
 *   no_ui = TRUE,
 *   serialized_property_names = {
 *     "value"
 *   }
 * )
 */
class MenuLinkContentVisibilityItem extends StringLongItem {

}
