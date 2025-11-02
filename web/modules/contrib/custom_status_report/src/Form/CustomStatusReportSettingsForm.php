<?php

namespace Drupal\custom_status_report\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Custom Status Report settings.
 */
class CustomStatusReportSettingsForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleList;

  /**
   * Constructs a CustomStatusReportSettingsForm object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_list
   *   The module extension list.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ModuleExtensionList $module_list) {
    $this->moduleHandler = $module_handler;
    $this->moduleList = $module_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('extension.list.module')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_status_report_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['custom_status_report.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[] = [
      '#type' => 'markup',
      '#markup' => $this->t('<a class="button" href="/admin/reports/status">View the Status Report page</a>.'),
    ];

    $form['card_visibility'] = [
      '#type' => 'details',
      '#title' => $this->t('General System Information Card Visibility'),
      '#description' => $this->t('Control which cards are displayed in the General System Information section.'),
      '#open' => TRUE,
    ];

    $form['card_visibility']['table-row'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('General System Information Card'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('Sorry, There are no items!'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
        ],
      ],
    ];

    $config = $this->config('custom_status_report.settings');

    // Get the default cards from Drupal core.
    $sections = [
      'drupal' => [
        'title' => $this->t('Drupal Version'),
        'weight' => $config->get('card_weight.drupal') ?? 0,
      ],
      'webserver' => [
        'title' => $this->t('Web Server'),
        'weight' => $config->get('card_weight.webserver') ?? 0,
      ],
      'cron' => [
        'title' => $this->t('Last Cron Run'),
        'weight' => $config->get('card_weight.cron') ?? 0,
      ],
      'php' => [
        'title' => $this->t('PHP Information'),
        'weight' => $config->get('card_weight.php') ?? 0,
      ],
      'database_system' => [
        'title' => $this->t('Database Information'),
        'weight' => $config->get('card_weight.database_system') ?? 0,
      ],
    ];

    // Add any custom modules that have implemented the
    // hook_requirements_alter().
    $other_modules = $this->getOtherModules();
    foreach ($other_modules as $key => $label) {
      $sections[$key] = [
        'title' => $label . ' <em>(custom)</em>',
        'weight' => $config->get('card_weight.' . $key) ?? 0,
      ];
    }

    uasort($sections, function ($a, $b) {
      return $a['weight'] <=> $b['weight'];
    });

    // Add each item to a draggable table.
    foreach ($sections as $key => $data) {
      $form['card_visibility']['table-row'][$key]['#attributes']['class'][] = 'draggable';
      $form['card_visibility']['table-row'][$key]['#weight'] = $data['weight'];
      $form['card_visibility']['table-row'][$key]['visibility'] = [
        '#type' => 'checkbox',
        '#title' => $data['title'],
        '#default_value' => $config->get("card_visibility.$key") ?? TRUE,
      ];

      $form['card_visibility']['table-row'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', [
          '@title' => $data['title'],
        ]),
        '#title_display' => 'invisible',
        '#default_value' => $data['weight'],
        '#attributes' => [
          'class' => [
            'table-sort-weight',
          ],
        ],
      ];
    }
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save All Changes'),
    ];
    $form['actions']['cancel'] = [
      '#type' => 'submit',
      '#value' => 'Cancel',
      '#attributes' => [
        'title' => $this->t('Cancel All Changes'),
      ],
      '#submit' => [
        '::cancel',
      ],
      '#limit_validation_errors' => [],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('custom_status_report.settings');
    $values = $form_state->getValues();

    foreach ($values['table-row'] as $section => $value) {
      if (!in_array($section, ['form_build_id', 'form_token', 'form_id', 'op', 'submit'])) {
        $config->set("card_visibility.$section", $value['visibility']);
        $config->set("card_weight.$section", $value['weight']);
      }
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Form submission handler for the 'Return to' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function cancel(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('custom_status_report.settings');
  }

  /**
   * Get all other modules who have implemented 'add_to_general_info'.
   */
  private function getOtherModules() {
    $implementors = [];
    foreach ($this->moduleHandler->getModuleList() as $module => $info) {
      $has_hook = $this->moduleHandler->hasImplementations('requirements_alter', $module);
      if ($has_hook) {
        $implementors[$module] = $this->moduleList->getName($module);
      }
    }
    return $implementors;
  }

}
