<?php

namespace Drupal\config_patch\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\config_patch\ConfigCompare;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;

/**
 * Defines a confirmation form to confirm revert of something by id.
 */
class ConfirmRevertForm extends ConfirmFormBase {

  /**
   * IDs of the items to revert.
   *
   * @var string
   */
  protected $configNames;

  /**
   * @var \Drupal\config_patch\ConfigCompare
   *   The config comparison service.
   */
  protected $configCompare;

  /**
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   *   The config comparison service.
   */
  protected $cacheTagInvalidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigCompare $configCompare, CacheTagsInvalidatorInterface $cacheTagsInvalidator) {
    $this->configCompare = $configCompare;
    $this->cacheTagInvalidator = $cacheTagsInvalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config_patch.config_compare'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $config_names = '') {
    $this->configNames = explode('|', $config_names);
    $form_state->set('config_names', $this->configNames);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->get('config_names') as $item) {
      if (strpos($item, ':') !== FALSE) {
        list($collection, $item) = explode(':', $item);
      }
      $this->configCompare->revert($item, $collection);
    }
    $this->cacheTagInvalidator->invalidateTags(['config_patch']);
    $form_state->setRedirect('config.patch');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() : string {
    return "config_patch_confirm_revert_form";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return Url::fromRoute('config.patch');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to revert %config_name?', ['%config_name' => implode(', ', $this->configNames)]);
  }

}