<?php

namespace Drupal\entity_usage_explorer\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ViewExecutable;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide a custom field to list entity usage.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("base_entity_usage_views_field")
 */
class BaseEntityUsageField extends FieldPluginBase {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * The entity usage service.
   *
   * @var \Drupal\entity_usage_explorer\UsageService
   */
  protected $entityUsageService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new self($configuration, $plugin_id, $plugin_definition);
    $instance->entityUsageService = $container->get('entity_usage_explorer.usage');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, ?array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->currentDisplay = $view->current_display;
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Add option to select rendering type: 'plain_text' or 'link'.
    $options['render_type'] = ['default' => 'plain_text'];
    $options['hide_alter_empty'] = ['default' => FALSE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['render_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Render Type'),
      '#description' => $this->t('Choose how you want to render the count.'),
      '#options' => [
        'plain_text' => $this->t('Plain Text'),
        'link' => $this->t('Link to usage overview page'),
      ],
      '#default_value' => $this->options['render_type'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    $id = $entity->id();
    $entity_type = $entity->getEntityTypeId();
    $count = $this->entityUsageService->getEntityUsageCount($entity_type, $id);

    if ($this->options['render_type'] === 'link') {
      return [
        '#type' => 'link',
        '#title' => $count,
        '#url' => Url::fromRoute('entity_usage_explorer.usage_page', ['entity_type' => $entity_type, 'entity_id' => $id]),
      ];
    }
    else {
      return [
        '#markup' => $count,
      ];
    }
  }

}
