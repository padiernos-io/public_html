<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Entity\BackstopProfile;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting Backstop scenarios.
 *
 * @package Drupal\backstop_generator\Form
 */
class BackstopScenarioDeleteForm extends EntityConfirmFormBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new BackstopScenarioDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the %label scenario?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return $this->t('Delete Scenario');
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Delete the entity.
    $this->entity->delete();
    $this->messenger()->addMessage(t('The %name scenario has been deleted.', ['%name' => $this->entity->label()]));
    parent::submitForm($form, $form_state);

    // Update any profile using this viewport.
    $updated_profiles = $this->updateProfiles();
    $update_message = count($updated_profiles) > 0 ?
      t('Updated %label backstop.json profile file.', ['%label' => implode(', ', $updated_profiles)]) :
      t('No profiles needed to be updated.');
    $this->messenger()->addMessage($update_message);
  }

  /**
   * Update the profiles that use this scenario.
   *
   * @return array
   *   The updated profiles.
   */
  private function updateProfiles() {
    $updated_profiles = [];

    // Get the profile config ids.
    $profile_ids = $this->entityTypeManager
      ->getStorage('backstop_profile')
      ->getQuery()
      ->accessCheck()
      ->execute();

    foreach ($profile_ids as $id) {
      // Get the profile config.
      $profile_config = $this->configFactory()->getEditable("backstop_generator.profile.$id");

      if (in_array($this->entity->id(), $profile_config->get('scenarios'), TRUE)) {
        // Remove the viewport from the profile config.
        $viewports = $profile_config->get('scenarios');
        unset($viewports[$this->entity->id()]);
        $profile_config->set('scenarios', $viewports);
        $profile_config->save();

        // Update the backstop.json file.
        $profile = BackstopProfile::load($id);
        $profile->generateBackstopFile($id);
        $updated_profiles[] = $profile->label();
      }
    }

    return $updated_profiles;
  }

}
