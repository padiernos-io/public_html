<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Element;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Attribute\FormElement;
use Drupal\Core\Render\Element\FormElementBase;
use Drupal\entity_display_processor\Callback\ElementAjax\AjaxReplaceCallback;
use Drupal\entity_display_processor\Callback\IdToSubform\PluginIdToSubform;

/**
 * Form element type for a drilldown element.
 *
 * The drilldown has a select element, and then a sub-form that changes based on
 * the selected id, using ajax with a dynamic callback function.
 *
 * The element properties are not documented, simply use the static factories.
 *
 * @internal
 *   This might be renamed or moved to a different module any time.
 */
#[FormElement(self::TYPE)]
class Drilldown extends FormElementBase {

  public const string TYPE = 'entity_display_processor_drilldown';

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    return [
      '#tree' => TRUE,
      '#input' => TRUE,
      '#process' => [
        [static::class, 'process'],
      ],
      '#id_key' => 'id',
      '#settings_key' => 'settings',
      '#select_options' => [],
      '#create_subform' => NULL,
    ];
  }

  /**
   * Creates a form element from a plugin manager object.
   *
   * @param string|\Drupal\Component\Render\MarkupInterface|null $title
   *   Title for the select element.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $plugin_manager
   *   Plugin manager.
   *   This should be a plugin type where definitions are arrays with 'label'
   *   key. Otherwise, the labels will just be machine names.
   * @param string|int|null $id
   *   Default value for the select element.
   * @param array $settings
   *   Default value for the sub-form.
   * @param string $id_key
   *   Settings key that holds the id.
   * @param string $settings_key
   *   Settings key that holds the subform settings.
   *
   * @return array
   *   A form element.
   */
  public static function createElementFromPluginManager(
    string|MarkupInterface|null $title,
    PluginManagerInterface $plugin_manager,
    string|int|null $id,
    array $settings,
    string $id_key = 'id',
    string $settings_key = 'settings',
  ): array {
    $options = [];
    foreach ($plugin_manager->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = is_array($definition)
        ? ($definition['label'] ?? $plugin_id)
        : $plugin_id;
    }
    $create_subform = new PluginIdToSubform($plugin_manager);
    return static::createElement(
      $title,
      $options,
      $create_subform,
      $id,
      $settings,
      $id_key,
      $settings_key,
    );
  }

  /**
   * Creates a form element with this element type.
   *
   * @param string|\Drupal\Component\Render\MarkupInterface|null $title
   *   Title for the select element.
   * @param array<int|string, int|string|\Drupal\Component\Render\MarkupInterface|array<int|string, int|string|\Drupal\Component\Render\MarkupInterface> $options
   *   Options for the select element.
   * @param callable(int|string, array, \Drupal\Core\Form\SubformStateInterface): array $create_subform
   *   Callback to create a subform.
   * @param string|int|null $id
   *   Default value for the select element.
   * @param array $settings
   *   Default value for the sub-form.
   * @param string $id_key
   *   Settings key that holds the id.
   * @param string $settings_key
   *   Settings key that holds the subform settings.
   *
   * @return array
   *   A form element.
   */
  public static function createElement(
    string|MarkupInterface|null $title,
    array $options,
    callable $create_subform,
    string|int|null $id,
    array $settings,
    string $id_key = 'id',
    string $settings_key = 'settings',
  ): array {
    return [
      '#type' => self::TYPE,
      '#title' => $title,
      '#create_subform' => $create_subform,
      '#select_options' => $options,
      '#default_value' => [
        $id_key => $id,
        $settings_key => $settings,
      ],
    ];
  }

  /**
   * Element '#process' callback.
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   *
   * @return array
   */
  public static function process(array $element, FormStateInterface $form_state, array $complete_form): array {
    $id_key = $element['#id_key'];
    $settings_key = $element['#settings_key'];
    $value = is_array($element['#value']) ? $element['#value'] : [];
    $id = $value[$id_key] ?? '';
    $element['id'] = [
      '#parents' => [...$element['#parents'], $id_key],
      '#type' => 'select',
      '#options' => $element['#select_options'],
      '#title' => $element['#title'],
      '#default_value' => $id,
      '#empty_value' => '',
    ];
    $ajax_id = md5(serialize($element['#parents']));
    $element['id']['#ajax'] = [
      'callback' => new AjaxReplaceCallback([
        ...$element['#array_parents'],
        'subform_container',
      ]),
      'wrapper' => $ajax_id,
    ];
    $element['subform_container'] = [
      '#type' => 'container',
      '#parents' => $element['#parents'],
      '#attributes' => [
        'id' => $ajax_id,
        'data-current-id' => $id,
      ],
    ];
    $element['subform_container']['subform'] = [];
    $subform_callback = static::getSubformCallback($element);
    $subform_state = SubformState::createForSubform($element['subform_container']['subform'], $complete_form, $form_state);
    if ($id !== NULL) {
      $subform_value = $value[$settings_key] ?? [];
      $subform = $subform_callback($id, $subform_value, $subform_state);
      $subform['#parents'] = [...$element['#parents'], $settings_key];
      $element['subform_container']['subform'] = $subform;
    }
    return $element;
  }

  /**
   * Gets the subform callback from the '#create_subform' element property.
   *
   * @param array $element
   *
   * @return callable(int|string, array, \Drupal\Core\Form\SubformStateInterface): array
   */
  protected static function getSubformCallback(array $element): callable {
    $subform_callback = $element['#create_subform'] ?? NULL;
    if (!is_callable($subform_callback)) {
      throw new \RuntimeException(sprintf(
        'The #create_subform callback must be callable, found %s.\nElement #parents: %s.\nElement #array_parents: %s.',
        get_debug_type($subform_callback),
        implode('/', $element['#parents']),
        implode('/', $element['#array_parents']),
      ));
    }
    return $subform_callback;
  }

}
