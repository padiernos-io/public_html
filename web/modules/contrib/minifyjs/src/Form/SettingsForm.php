<?php

namespace Drupal\minifyjs\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\minifyjs\MinifyJsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form for the module.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected CacheBackendInterface $cache;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cache.default')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   */
  public function __construct(CacheBackendInterface $cache) {
    $this->cache = $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->config('minifyjs.config');

    $form['disable_admin'] = [
      '#title' => $this->t('Disable on admin pages.'),
      '#description' => $this->t('Disable this module functionality on admin pages.'),
      '#type' => 'checkbox',
      '#default_value' => $config->get('disable_admin'),
    ];

    $form['exclusion_list'] = [
      '#title' => $this->t('Exclusion List'),
      '#description' => $this->t('Some files cannot be minified, for whatever reason. This list allows the administrator to exclude these files from the Manage Javascript Files page and stops the site from using the minified version of the file (if applicable). Allows wildcards (*) and other Drupal path conventions.'),
      '#type' => 'textarea',
      '#default_value' => $config->get('exclusion_list'),
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyjs_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('minifyjs.config')
      ->set('exclusion_list', $form_state->getValue('exclusion_list'))
      ->set('disable_admin', $form_state->getValue('disable_admin'))
      ->save();

    // Clear the cache.
    $this->cache->delete(MinifyJsInterface::MINIFYJS_CACHE_CID);
    $this->messenger()->addMessage($this->t('Settings updated successfully.'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['minifyjs.config'];
  }

}
