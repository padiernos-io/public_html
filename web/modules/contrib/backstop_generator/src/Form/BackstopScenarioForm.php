<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopProfile;
use Drupal\backstop_generator\Services\BackstopFormBuilder;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Backstop Scenario form.
 *
 * @property \Drupal\backstop_generator\BackstopScenarioInterface $entity
 */
class BackstopScenarioForm extends EntityForm {

  /**
   * The field map used to modify values prior to saving.
   *
   * @var array
   */
  protected $fieldMap = [
    'label',
    'id',
    'url',
    'referenceUrl',
    'bundle',
    'delay',
    'useScenarioDefaults',
    'hideSelectors',
    'removeSelectors',
    'misMatchThreshold',
    'readyEvent',
    'readySelector',
    'readyTimeout',
    'onReadyScript',
    'onBeforeScript',
    'keyPressSelectors',
    'hoverSelector',
    'hoverSelectors',
    'clickSelector',
    'clickSelectors',
    'postInteractionWait',
    'cookiePath',
    'scrollToSelector',
    'selectors',
    'selectorExpansion',
    'expect',
    'goToParameters',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The BackstopFormBuilder service.
   *
   * @var \Drupal\backstop_generator\Services\BackstopFormBuilder
   */
  protected $backstopFormBuilder;

  /**
   * Constructs a new BackstopScenarioForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\backstop_generator\Services\BackstopFormBuilder $backstop_form_builder
   *   The BackstopFormBuilder service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    BackstopFormBuilder $backstop_form_builder,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->backstopFormBuilder = $backstop_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('backstop_generator.form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $server = $_SERVER;
    $site = "{$server['REQUEST_SCHEME']}://{$server['SERVER_NAME']}";
    $backstop_settings = $this->configFactory()->get('backstop_generator.settings');

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->entity->get('label'),
      '#autocomplete_route_name' => 'backstop_generator.autocomplete',
      '#description' => t('select a label from the autocomplete.'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::populate',
        'event' => 'autocompleteclose',
      ],
      '#attributes' => [
        'disabled' => !$this->entity->isNew(),
      ],
    ];

    $form['id'] = [
      '#type' => 'textfield',
      '#default_value' => $this->entity->id(),
      '#attributes' => [
        'hidden' => 'hidden',
        'disabled' => !$this->entity->isNew(),
      ],
    ];

    $form['override_profile_domains'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override Profile Domains'),
      '#default_value' => $this->entity->get('override_profile_domains') ?? FALSE,
      '#description' => $this->t('Checking this box allows you to edit the url and referenceUrl fields.'),
    ];

    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('url'),
      '#default_value' => $this->entity->get('url') ?? $site,
      '#description' => $this->t('The URL of the page you want to test in this scenario.'),
      '#description_display' => 'before',
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => $backstop_settings->get('url') . '/node/[nid]',
        'disabled' => !$form_state->getValue('override_profile_domains'),
      ],
      '#states' => [
        'disabled' => [
          ':input[name="override_profile_domains"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['referenceUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('referenceUrl'),
      '#default_value' => $this->entity->get('referenceUrl'),
      '#description' => $this->t('The domain you want to test against (source of truth) - no trailing slash.'),
      '#description_display' => 'before',
      '#attributes' => [
        'placeholder' => $backstop_settings->get('referenceUrl') . '/node/[nid]',
        'disabled' => !$form_state->getValue('override_profile_domains'),
      ],
      '#states' => [
        'disabled' => [
          ':input[name="override_profile_domains"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['bundle'] = [
      '#type' => 'textfield',
      '#default_value' => $this->entity->get('bundle'),
      '#attributes' => [
        'hidden' => 'hidden',
        'disabled' => !$this->entity->isNew(),
      ],
    ];

    $form['useScenarioDefaults'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use scenarioDefaults'),
      '#default_value' => $this->entity->get('useScenarioDefaults') ?? TRUE,
      '#description' => $this->t('Unchecking this box will make the settings available.'),
    ];

    $defaults = $this->entity->toArray();
    $profile_id = $this->entity->get('profile_id');
    $form = $this->backstopFormBuilder->buildScenarioDefaultsSection($form, $defaults, $profile_id);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Validate the value of the hidden 'bundle' and 'id' fields
    // since they are populated when a label is selected.
    $id = $form_state->getValue('id');
    $bundle = $form_state->getValue('bundle');

    if ($id === '' || $bundle === '') {
      $form_state->setErrorByName(
        'label',
        $this->t('Please add a valid label from the autocomplete.')
      );
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new backstop scenario %label.', $message_args)
      : $this->t('Updated backstop scenario %label.', $message_args);
    $this->messenger()->addStatus($message);

    $profile_id = $this->entity->get('profile_id');
    BackstopProfile::load($profile_id)->generateBackstopFile();
    $profile_label = $this->configFactory()
      ->get("backstop_generator.profile.{$profile_id}")
      ->get('label');
    $this->messenger()->addMessage($this->t('The %profile profile has been updated.', ['%profile' => $profile_label]));

    return $result;
  }

  /**
   * AJAX callback: populates hidden/readonly fields based on label field input.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response object.
   */
  public function populate(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $label = $form_state->getValue('label');
    // Isolate the nid from the label field.
    preg_match('/([\w\s]+) \((\d+)\)$/', $label, $nid);
    $node = Node::load($nid[2]);

    if (!empty($label) && isset($nid[1])) {
      // Set the value of the bundle field.
      $response->addCommand(new InvokeCommand('#edit-bundle', 'val', [$node->bundle()]));
      // Set the value of url using the nid value.
      if (!preg_match('/node\/\d+$/', $this->entity->get('url'))) {
        $response->addCommand(new InvokeCommand('#edit-url', 'val', ["{$this->entity->get('url')}/node/$nid[2]"]));
      }
      else {
        preg_match('/([\:\w\.\/-]+)\/\d+$/', $this->entity->get('url'), $url);
        $response->addCommand(new InvokeCommand('#edit-url', 'val', ["$url[1]/$nid[2]"]));
      }
      // Set the value of id to the path.
      $response->addCommand(new InvokeCommand('#edit-id', 'val', ["node-$nid[2]"]));
    }
    return $response;
  }

  /**
   * Updates the Profiles using this Scenario.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function updateProfiles() {
    $profile = BackstopProfile::load($this->entity->get('profile_id'));
    $profile->generateBackstopFile();
  }

}
