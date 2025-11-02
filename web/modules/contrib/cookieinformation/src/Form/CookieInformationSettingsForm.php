<?php

namespace Drupal\cookieinformation\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cookieinformation\CategoryService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Cookie Information settings.
 */
class CookieInformationSettingsForm extends ConfigFormBase {
  /**
   * The cookieinformation categories service.
   *
   * @var \Drupal\cookieinformation\CategoryService
   */
  protected CategoryService $categoryService;


  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'cookieinformation.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cookie_information_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * Construct the Cookie information settings form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *   The typed config manager.
   * @param \Drupal\cookieinformation\CategoryService $category_service
   *   The category service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $typed_config_manager, CategoryService $category_service) {
    parent::__construct($config_factory, $typed_config_manager);
    $this->categoryService = $category_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('cookieinformation.category_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['enable_popup'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable consent popup', [], ['context' => 'Cookie information']),
      '#description' => $this->t('Check to enable the Cookie Information consent popup.', [], ['context' => 'Cookie information']),
      '#default_value' => $config->get('enable_popup'),
    ];
    $form['enable_iab'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable IAB', [], ['context' => 'Cookie information']),
      '#description' => $this->t('Check to enable the IAB (Interactive Advertising Bureau) feature in the Cookie Information consent popup. You will need to use the correct template for the IAB to work. You can switch to the IAB template within the platform.', [], ['context' => 'Cookie information']),
      '#default_value' => $config->get('enable_iab'),
      '#states' => [
        'visible' => [
          ':checkbox[name="enable_popup"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['google_consent_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Google Consent Mode'),
      '#description' => $this->t('Select the version of Google Consent Mode you want to enable.'),
      '#options' => [
        '' => 'Disabled',
        'v1' => 'Google Consent Mode v1',
        'v2' => 'Google Consent Mode v2',
      ],
      '#default_value' => $config->get('google_consent_mode'),
    ];
    $form['block_iframes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Block iframes', [], ['context' => 'Cookie information']),
      '#description' => $this->t('Check to block iframes when user has not accepted functional cookies', [], ['context' => 'Cookie information']),
      '#default_value' => $config->get('block_iframes'),
    ];
    $form['block_iframes_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Iframe blocking category', ['context' => 'Cookie information']),
      '#description' => $this->t('Select the category to be used when blocking iframes. Defaults to functional.', ['context' => 'Cookie information']),
      '#options' => $this->categoryService->getCategories(),
      '#default_value' => $config->get('block_iframes_category'),
    ];
    $form['visibility'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cookie Information consent popup visibility', [], ['context' => 'Cookie information']),
      '#states' => [
        'visible' => [
          ':checkbox[name="enable_popup"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['visibility']['exclude_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude paths', [], ['context' => 'Cookie information']),
      '#default_value' => !empty($config->get('exclude_paths')) ? $config->get('exclude_paths') : '',
      '#description' => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", [
        '%blog' => '/blog',
        '%blog-wildcard' => '/blog/*',
        '%front' => '<front>',
      ], ['context' => 'Cookie information']),
    ];
    $form['visibility']['exclude_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Exclude admin pages', [], ['context' => 'Cookie information']),
      '#description' => $this->t('Hide the Cookie Information consent popup on administration pages.', [], ['context' => 'Cookie information']),
      '#default_value' => $config->get('exclude_admin'),
    ];
    $form['visibility']['exclude_uid_1'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Don’t show the Cookie Information consent popup for UID 1.', [], ['context' => 'Cookie information']),
      '#description' => $this->t('Hide the Cookie Information consent popup for the user with UID 1.', [], ['context' => 'Cookie information']),
      '#default_value' => $config->get('exclude_uid_1'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::SETTINGS)
      ->set("enable_popup", $form_state->getValue('enable_popup'))
      ->set("enable_iab", $form_state->getValue('enable_iab'))
      ->set("google_consent_mode", $form_state->getValue('google_consent_mode'))
      ->set("block_iframes", $form_state->getValue('block_iframes'))
      ->set("block_iframes_category", $form_state->getValue('block_iframes_category'))
      ->set('exclude_paths', $form_state->getValue('exclude_paths'))
      ->set('exclude_admin', $form_state->getValue('exclude_admin'))
      ->set('exclude_uid_1', $form_state->getValue('exclude_uid_1'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
