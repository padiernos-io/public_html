<?php

namespace Drupal\backstop_generator\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a form to filter backstop scenarios.
 */
class BackstopScenarioFilterForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a new BackstopScenarioFilterForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RouteMatchInterface $route_match,
    RequestStack $request_stack,
    LoggerChannelFactoryInterface $logger_factory,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->requestStack = $request_stack;
    $this->logger = $logger_factory->get('backstop_generator');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('request_stack'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'entity_filter_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = $this->requestStack->getCurrentRequest();

    // Check query parameters first, otherwise fall back to session values.
    $profile_filter = $request->query->get('profile_filter', '');
    $label_filter = $request->query->get('label_filter', '');

    // Get a list of all profiles.
    $reports = $this->entityTypeManager->getStorage('backstop_profile')->loadMultiple();
    $report_options = [];
    foreach ($reports as $report) {
      $report_options[$report->id()] = $report->label();
    }
    // Add an option for all profiles.
    $report_options = ['' => $this->t('All profiles')] + $report_options;

    $form['profile_filter'] = [
      '#type' => 'select',
      '#title' => $this->t('Filter by profile'),
      '#options' => $report_options,
      '#default_value' => $profile_filter,
    ];

    $form['label_filter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Filter by label'),
      '#default_value' => $label_filter,
      '#size' => 30,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Apply filters'),
    ];

    $form['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset'),
      '#attributes' => [
        'id' => 'reset-filter-button',
      ],
      '#submit' => ['::resetForm'],
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->redirectToFilteredPage($form_state);
  }

  /**
   * Reset the form fields.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return void
   *   Resets the form filters and redirects to the filtered page.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setValue('profile_filter', '');
    $form_state->setValue('label_filter', '');
    $this->redirectToFilteredPage($form_state);
  }

  /**
   * Redirect to the page with query parameters.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return void
   *   Redirects to the filtered page.
   */
  private function redirectToFilteredPage(FormStateInterface $form_state) {
    $query = [
      'profile_filter' => $form_state->getValue('profile_filter'),
      'label_filter' => $form_state->getValue('label_filter'),
    ];
    // Get the correct route dynamically.
    $route_name = $this->routeMatch->getRouteName();

    // Ensure the route exists before redirecting.
    if ($route_name) {
      $form_state->setRedirect($route_name, [], ['query' => $query]);
    }
    else {
      $this->logger('backstop_generator')
        ->error('Could not determine the correct route for filtering.');
    }
  }

}
