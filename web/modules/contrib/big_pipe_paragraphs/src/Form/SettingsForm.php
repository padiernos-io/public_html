<?php

namespace Drupal\big_pipe_paragraphs\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The geocoder settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entityTypeBundleInfo
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityFieldManagerInterface $entityFieldManager,
    EntityTypeBundleInfoInterface $entityTypeBundleInfo
  ) {
    parent::__construct($config_factory);
    $this->entityFieldManager = $entityFieldManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'big_pipe_paragraphs_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['geocoder.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('big_pipe_paragraphs.settings');

    $entityFieldTypeMapping = $this->entityFieldManager->getFieldMapByFieldType('entity_reference_revisions');

    $paragraphBundles = $this->entityTypeBundleInfo->getBundleInfo('paragraph');
    $paragraphBundleOptions = array_combine(array_keys($paragraphBundles), array_map(static function ($bundleInfo) {
      return $bundleInfo['label'];
    }, $paragraphBundles));

    $form['entity_type'] = [
      '#tree' => TRUE,
    ];

    foreach ($entityFieldTypeMapping as $entityTypeId => $fields) {
      if (empty($fields)) {
        continue;
      }

      $form['entity_type'][$entityTypeId] = [
        '#title' => $entityTypeId,
        '#type' => 'details',
        '#open' => FALSE,
        '#description' => $this->t('All paragraph field big pipe settings for this entity type.'),
      ];

      foreach ($fields as $fieldName => $fieldInfo) {
        $form['entity_type'][$entityTypeId][$fieldName] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $fieldName,
          '#description' => $this->t('All big pipe settings for paragraphs within this field.'),
        ];

        $bundles = $config->get(sprintf('entity_type.%s.%s.entity_bundles', $entityTypeId, $fieldName));
        $element['entity_bundles'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Bundles'),
          '#description' => $this->t('For which bundles of this entity type should this be enabled.'),
          '#options' => $fieldInfo['bundles'],
          '#default_value' => !empty($bundles) ? $bundles : [],
        ];

        $offset = $config->get(sprintf('entity_type.%s.%s.offset', $entityTypeId, $fieldName));
        $element['offset'] = [
          '#type' => 'number',
          '#title' => $this->t('Offset'),
          '#description' => $this->t('After this offset the paragraphs of a paragraph field will be lazy loaded through big pipe.'),
          '#min' => 0,
          '#default_value' => $offset,
        ];

        $skip = $config->get(sprintf('entity_type.%s.%s.skip_bundles', $entityTypeId, $fieldName));
        $element['skip_bundles'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Skip paragraph types'),
          '#description' => $this->t('The paragraph types that should not be loaded through big pipe.'),
          '#options' => $paragraphBundleOptions,
          '#default_value' => !empty($skip) ? $skip : [],
        ];

        $form['entity_type'][$entityTypeId][$fieldName] = array_merge($form['entity_type'][$entityTypeId][$fieldName], $element);
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get all the form state values, in an array structure.
    $values = $form_state->getValues();
    $entityTypeValueSets = $values['entity_type'];

    foreach ($entityTypeValueSets as $entityTypeId => $entityTypeValueSet) {
      $entityTypeValueSets[$entityTypeId] = $this->filteredEntityTypeValueSet($entityTypeValueSet);
      if (!empty($entityTypeValueSet)) {
        continue;
      }
      unset($entityTypeValueSets[$entityTypeId]);
    }

    $config = $this->configFactory->getEditable('big_pipe_paragraphs.settings');
    $config->set('entity_type', $entityTypeValueSets);
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Filter unnecessary data from array.
   *
   * @param array $entityTypeValueSet
   *
   * @return array
   */
  private function filteredEntityTypeValueSet(array $entityTypeValueSet): array {
    foreach ($entityTypeValueSet as $field_name => $fieldValueSet) {
      $entityTypeValueSet[$field_name] = $this->filteredFieldValueSet($fieldValueSet);
      if ($fieldValueSet['offset'] !== '' || !empty($fieldValueSet['entity_bundles'])) {
        continue;
      }

      unset($entityTypeValueSet[$field_name]);
    }
    return $entityTypeValueSet;
  }

  /**
   * Filter unnecessary data from array.
   *
   * @param array $fieldValueSet
   *
   * @return array
   */
  private function filteredFieldValueSet(array $fieldValueSet): array {
    $fieldValueSet['entity_bundles'] = array_filter($fieldValueSet['entity_bundles']);
    $fieldValueSet['skip_bundles'] = array_filter($fieldValueSet['skip_bundles']);
    return $fieldValueSet;
  }

}
