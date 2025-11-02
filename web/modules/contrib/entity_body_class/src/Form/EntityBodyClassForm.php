<?php

namespace Drupal\entity_body_class\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for adding body class settings.
 */
class EntityBodyClassForm extends ConfigFormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->entityTypeManager = $container->get('entity_type.manager');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_body_class';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'entity_body_class.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('entity_body_class.settings');

    $form['types'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Default body classes'),
      '#tree' => TRUE,
    ];

    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      if (in_array(ContentEntityInterface::class, class_implements($definition->getOriginalClass())) &&
        $definition->getLinkTemplate('canonical')
      ) {
        $id = $definition->id();
        $label = $definition->getLabel() instanceof TranslatableMarkup ? $definition->getLabel()->render() : $definition->getLabel();
        $form['types'][$id] = [
          '#type' => 'textfield',
          '#title' => $label,
          '#default_value' => !empty($config->get('types')[$id]) ? $config->get('types')[$id] : '',
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable('entity_body_class.settings')
      ->set('types', array_filter($form_state->getValue('types')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
