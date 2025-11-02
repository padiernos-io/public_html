<?php

namespace Drupal\menu_link_content_visibility\Plugin\Field\FieldWidget;

use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * MenuLinkContentVisibility Widget.
 *
 * @FieldWidget(
 *   label = @Translation("Menu link visibility"),
 *   id = "menu_link_content_visibility",
 *   field_types = {
 *     "menu_link_content_visibility"
 *   },
 * )
 */
class MenuLinkContentVisibilityWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $conditionManager;

  /**
   * The global context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * Constructs a MenuLinkContentVisibilityWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition plugin manager.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The global context repository service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, ExecutableManagerInterface $condition_manager, ContextRepositoryInterface $context_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->conditionManager = $condition_manager;
    $this->contextRepository = $context_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.condition'),
      $container->get('context.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Array of visibility condition configurations.
    $value = $items[$delta]->value;
    if (!empty($value)) {
      $visibility = unserialize($value);
    }

    $element['visibility_tabs'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Visibility'),
      '#parents' => ['visibility_tabs'],
    ];

    $contexts = $this->contextRepository->getAvailableContexts();
    $form_state->setTemporaryValue('gathered_contexts', $contexts);

    foreach ($this->conditionManager->getDefinitionsForContexts($contexts) as $condition_id => $definition) {
      if ($condition_id === 'current_theme') {
        continue;
      }

      // gtag does something special with contexts, ignore them for now
      if (in_array($condition_id, ['gtag_domain', 'gtag_language'])) {
        continue;
      }

      /** @var \Drupal\Core\Condition\ConditionInterface $condition */
      $condition = $this->conditionManager->createInstance($condition_id);
      $condition_configuration = isset($visibility[$condition_id]) ? $visibility[$condition_id] : $condition->defaultConfiguration();
      $condition->setConfiguration($condition_configuration);

      // Build condition plugin form.
      $condition_form = $condition->buildConfigurationForm([], $form_state);
      $condition_form['#type'] = 'details';
      $condition_form['#title'] = $condition->getPluginDefinition()['label'];
      $condition_form['#group'] = 'visibility_tabs';

      $element[$condition_id] = $condition_form;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    foreach ($values as $delta => $conditions) {
      unset($conditions['_original_delta']);

      foreach ($conditions as $condition_id => $condition_configuration) {
        /** @var \Drupal\Core\Condition\ConditionInterface $condition */
        $condition = $this->conditionManager->createInstance($condition_id);
        $condition->setConfiguration($condition_configuration);

        $subform = $form[$field_name]['widget'][$delta][$condition_id];
        $subform_state = SubformState::createForSubform($subform, $form, $form_state);
        $condition->submitConfigurationForm($subform, $subform_state);

        // Remove empty values.
        // @todo This should really be handled by the plugin itself.
        $comparable_configuration = $condition->getConfiguration();
        unset($comparable_configuration['id']);
        unset($comparable_configuration['context_mapping']);
        if ($comparable_configuration != $condition->defaultConfiguration()) {
          $value[$condition_id] = $condition->getConfiguration();
        }
        else {
          unset($conditions[$condition_id]);
        }
      }

      if (!empty($value)) {
        $values[$delta] = serialize($value);
      }
      else {
        unset($values[$delta]);
      }
    }

    return $values;
  }

}
