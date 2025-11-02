<?php

namespace Drupal\form_decorator;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormValidatorInterface;
use Drupal\Core\Form\FormSubmitterInterface;
use Drupal\Core\Form\FormCacheInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Access\CsrfTokenGenerator;

/**
 * Add decorators to form objects.
 */
class FormDecoratorFormBuilder extends FormBuilder {

  /**
   * The form decorator plugin manager.
   *
   * @var \Drupal\form_decorator\FormDecoratorPluginManager
   */
  protected $formDecoratorManager;

  /**
   * Constructs a new FormDecoratorFormBuilder.
   *
   * @param \Drupal\Core\Form\FormValidatorInterface $form_validator
   *   The form validator.
   * @param \Drupal\Core\Form\FormSubmitterInterface $form_submitter
   *   The form submitter.
   * @param \Drupal\Core\Form\FormCacheInterface $form_cache
   *   The form cache.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $class_resolver
   *   The class resolver.
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   The element info manager.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrf_token
   *   The CSRF token generator.
   * @param \Drupal\form_decorator\FormDecoratorPluginManager $form_decorator_manager
   *   The form decorator plugin manager.
   */
  public function __construct(
    FormValidatorInterface $form_validator,
    FormSubmitterInterface $form_submitter,
    FormCacheInterface $form_cache,
    ModuleHandlerInterface $module_handler,
    EventDispatcherInterface $event_dispatcher,
    RequestStack $request_stack,
    ClassResolverInterface $class_resolver,
    ElementInfoManagerInterface $element_info,
    ThemeManagerInterface $theme_manager,
    CsrfTokenGenerator $csrf_token,
    FormDecoratorPluginManager $form_decorator_manager,
  ) {
    parent::__construct($form_validator, $form_submitter, $form_cache, $module_handler, $event_dispatcher, $request_stack, $class_resolver, $element_info, $theme_manager, $csrf_token);
    $this->formDecoratorManager = $form_decorator_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId($form_arg, FormStateInterface &$form_state) {
    // Unfortunately we have to extend the FormBuilder class.
    // The buildForm method directly calls this method.
    parent::getFormId($form_arg, $form_state);
    $form_arg = $form_state->getFormObject();

    $definitions = $this->formDecoratorManager->getDefinitions();

    // Decorate Forms that have a matching hook.
    foreach (array_keys($definitions) as $id) {
      /** @var \Drupal\form_decorator\FormDecoratorInterface $instance */
      $instance = $this->formDecoratorManager->createInstance($id);
      $instance->setInner($form_arg);
      if ($instance->applies()) {
        $form_arg = $instance;
      }
    }

    $form_state->setFormObject($form_arg);
    return $form_arg->getFormId();
  }

}
