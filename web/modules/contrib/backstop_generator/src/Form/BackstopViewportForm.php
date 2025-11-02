<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopProfile;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Backstop Viewport form.
 *
 * @property \Drupal\backstop_generator\BackstopViewportInterface $entity
 */
class BackstopViewportForm extends EntityForm {

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
   * Constructs a new BackstopScenarioForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#description' => $this->t('Label for the backstop viewport.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\backstop_generator\Entity\BackstopViewport::load',
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Viewport Width'),
      '#default_value' => $this->entity->get('width'),
    ];

    $form['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Viewport Height'),
      '#default_value' => $this->entity->get('height'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $message = $result == SAVED_NEW
      ? $this->t('Created new backstop viewport %label.', $message_args)
      : $this->t('Updated backstop viewport %label.', $message_args);
    $this->messenger()->addStatus($message);
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));

    $updated_profiles = $this->updateProfiles();
    $update_message = count($updated_profiles) > 0 ?
     $this->t('Updated %label backstop.json profile file.', ['%label' => implode(', ', $updated_profiles)]) :
     $this->t('No profiles needed to be updated.');
    $this->messenger->addMessage($update_message);

    return $result;
  }

  /**
   * Updates test Profiles that use this viewport.
   *
   * @return array
   *   An indexed array of updated profile names.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function updateProfiles() {
    // Get the profile config ids.
    $profile_ids = $this->entityTypeManager
      ->getStorage('backstop_profile')
      ->getQuery()
      ->accessCheck()
      ->execute();
    $updated_profiles = [];

    foreach ($profile_ids as $id) {
      // Get the profile config.
      $profile_config = $this->configFactory->getEditable("backstop_generator.profile.$id");
      if (in_array($this->entity->id(), $profile_config->get('viewports'), TRUE)) {
        // Update the backstop.json file.
        $profile = BackstopProfile::load($id);
        $profile->generateBackstopFile($id);
        $updated_profiles[] = $profile->label();
      }
    }
    return $updated_profiles;
  }

}
