<?php

namespace Drupal\patreon\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\patreon\PatreonServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to store API Client and Secret.
 *
 * @package Drupal\patreon\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'patreon.settings',
    ];
  }

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\patreon\PatreonServiceInterface $service
   *   A Patreon API service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   */
  public function __construct(
    protected readonly PatreonServiceInterface $service,
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typedConfigManager,
  ) {
    return parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): SettingsForm {
    return new static(
      $container->get('patreon.api'),
      $container->get('config.factory'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('patreon.settings');
    $form['oauth'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('OAuth Settings'),
      '#description' => $this->t('To enable OAuth based access for patreon, you must <a href="@url">register this site</a> with Patreon and add the provided keys here. When asked, you should specify you are using Version 2 of the API.',
        [
          '@url' => PatreonServiceInterface::PATREON_REGISTER_OAUTH_URL,
        ],
      ),
    ];
    $form['oauth']['endpoint'] = [
      '#markup' => $this->t('<p>When registering with Patreon, you must add @url as your application endpoint.</p>', [
        '@url' => $this->service->getCallback()->toString(),
      ]),
    ];
    $form['oauth']['patreon_client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patreon Client ID'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('patreon_client_id'),
      '#required' => TRUE,
    ];
    $form['oauth']['patreon_client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Patreon Client Secret'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('patreon_client_secret'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    parent::submitForm($form, $form_state);

    $this->config('patreon.settings')
      ->set('patreon_client_id', $form_state->getValue('patreon_client_id'))
      ->set('patreon_client_secret', $form_state->getValue('patreon_client_secret'))
      ->save();

    $redirect = $this->service->authoriseAccount();
    $form_state->setResponse($redirect);
  }

}
