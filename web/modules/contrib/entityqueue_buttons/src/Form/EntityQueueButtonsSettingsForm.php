<?php

namespace Drupal\entityqueue_buttons\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure EntityQueue Buttons settings.
 */
class EntityQueueButtonsSettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityQueueButtonsSettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['entityqueue_buttons.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entityqueue_buttons_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('entityqueue_buttons.settings');

    // Get all node types.
    $node_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    if (empty($node_types)) {
      $form['no_content_types'] = [
        '#markup' => '<p>' . $this->t('No content types are available.') . '</p>',
      ];
      return parent::buildForm($form, $form_state);
    }

    // Get all entity queues.
    $queues = $this->entityTypeManager->getStorage('entity_queue')->loadMultiple();

    if (empty($queues)) {
      $form['no_queues'] = [
        '#markup' => '<p>' . $this->t('No entity queues are available. Please create at least one entity queue before configuring this module.') . '</p>',
      ];
      return parent::buildForm($form, $form_state);
    }

    $form['description'] = [
      '#markup' => '<p>' . $this->t('Select which entity queues should display add/remove buttons for each content type.') . '</p>',
    ];

    // Create a vertical tabs container.
    $form['content_types'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Content Types'),
    ];

    // Build queue options.
    $queue_options = [];
    foreach ($queues as $queue_id => $queue) {
      $queue_options[$queue_id] = $queue->label();
    }

    // Create a fieldset for each content type.
    foreach ($node_types as $type_id => $type) {
      $form['queue_settings_' . $type_id] = [
        '#type' => 'details',
        '#title' => $type->label(),
        '#group' => 'content_types',
      ];

      $form['queue_settings_' . $type_id]['queue_settings'][$type_id] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Available queues for @type', ['@type' => $type->label()]),
        '#options' => $queue_options,
        '#default_value' => $config->get("queue_settings.$type_id") ?: [],
        '#description' => $this->t('Users with the appropriate permissions will see buttons to add or remove @type nodes from the selected queues.', ['@type' => $type->label()]),
      ];
    }

    $form['permissions_note'] = [
      '#type' => 'item',
      '#markup' => '<div class="messages messages--warning">' .
        $this->t('<strong>Note:</strong> Users must have the "manipulate [queue_name] entityqueue" permission to see and use the buttons for each queue. Configure permissions at the <a href="@permissions">Permissions page</a>.', [
          '@permissions' => '/admin/people/permissions#module-entityqueue',
        ]) .
        '</div>',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('entityqueue_buttons.settings');

    // Get all node types to iterate through.
    $node_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();

    // Save queue settings for each content type.
    foreach ($node_types as $type_id => $type) {
      $queue_settings = $form_state->getValue([$type_id]);
      if ($queue_settings !== NULL) {
        $config->set("queue_settings.$type_id", $queue_settings);
      }
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}