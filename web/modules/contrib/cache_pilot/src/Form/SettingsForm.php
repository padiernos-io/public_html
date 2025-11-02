<?php

declare(strict_types=1);

namespace Drupal\cache_pilot\Form;

use Drupal\cache_pilot\Connection\ConnectionConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\cache_pilot\Cache\ApcuCache;
use Drupal\cache_pilot\Cache\OpcacheCache;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides settings for Cache Pilot module.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * Constructs a Settings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   *   The typed config manager.
   * @param \Drupal\cache_pilot\Cache\ApcuCache $apcuCache
   *   The APCu cache manager.
   * @param \Drupal\cache_pilot\Cache\OpcacheCache $opcacheCache
   *   The Zend OPcache cache manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    $typedConfigManager,
    protected readonly ApcuCache $apcuCache,
    protected readonly OpcacheCache $opcacheCache,
  ) {
    parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get(ConfigFactoryInterface::class),
      $container->get(TypedConfigManagerInterface::class),
      $container->get(ApcuCache::class),
      $container->get(OpcacheCache::class),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'cache_pilot_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $this->buildConnectionSettings($form, $form_state);
    $this->buildCacheClearSettings($form, $form_state);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Builds connection settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function buildConnectionSettings(array &$form, FormStateInterface $form_state): void {
    $form['connection'] = [
      '#type' => 'fieldset',
      '#title' => new TranslatableMarkup('Connection'),
      '#description' => new TranslatableMarkup('Set up the connection to PHP, which will be used to interact with the cache.'),
    ];

    $form['connection']['connection_dsn'] = [
      '#type' => 'textfield',
      '#title' => new TranslatableMarkup('Connection DSN'),
      '#description' => new TranslatableMarkup('The connection string in the format <code>tcp://[host]:[port]</code> for TCP or <code>unix://[socket_path]</code> for a Unix socket.'),
      '#config_target' => 'cache_pilot.settings:connection_dsn',
    ];
  }

  /**
   * Build cache clear settings.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  private function buildCacheClearSettings(array &$form, FormStateInterface $form_state): void {
    $form['cache_clear'] = [
      '#type' => 'fieldset',
      '#title' => new TranslatableMarkup('Cache clearing'),
    ];

    $form['cache_clear']['actions'] = [
      '#type' => 'actions',
    ];

    $form['cache_clear']['actions']['clear_apcu'] = [
      '#type' => 'submit',
      '#value' => new TranslatableMarkup('Clear APCu cache'),
      '#submit' => [$this->clearApcu(...)],
      '#disabled' => !$this->apcuCache->isEnabled(),
      '#button_type' => 'small',
    ];

    $form['cache_clear']['actions']['clear_opcache'] = [
      '#type' => 'submit',
      '#value' => new TranslatableMarkup('Clear Zend Opcache'),
      '#submit' => [$this->clearOpcache(...)],
      '#disabled' => !$this->opcacheCache->isEnabled(),
      '#button_type' => 'small',
    ];
  }

  /**
   * Attempts to clear APCu cache.
   */
  public function clearApcu(): void {
    $this->apcuCache->clear();
    $this->messenger()->addStatus(
      message: new TranslatableMarkup('APCu cache cleared.'),
    );
  }

  /**
   * Attempts to clear Zend OPcache.
   */
  public function clearOpcache(): void {
    $this->opcacheCache->clear();
    $this->messenger()->addStatus(
      message: new TranslatableMarkup('Zend Opcache cleared.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $connection_dsn = $form_state->getValue('connection_dsn');

    if (!$connection_dsn) {
      return;
    }

    try {
      \assert(is_string($connection_dsn));
      ConnectionConfig::fromDsn($connection_dsn);
    }
    catch (\InvalidArgumentException $exception) {
      $form_state->setError($form['connection']['connection_dsn'], $exception->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['cache_pilot.settings'];
  }

}
