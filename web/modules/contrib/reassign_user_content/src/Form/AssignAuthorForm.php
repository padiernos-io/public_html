<?php

declare(strict_types=1);

namespace Drupal\reassign_user_content\Form;

use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a re-assign content to another user.
 */
final class AssignAuthorForm extends FormBase {

  /**
   * Constructs a new AssignAuthorForm.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $tempstorePrivate
   *   The private temp store factory service.
   */
  public function __construct(
    private readonly ModuleHandler $moduleHandler,
    private readonly PrivateTempStoreFactory $tempstorePrivate,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'reassign_user_content_assign_author';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['user_to_assign'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Choose user to assign'),
      '#target_type' => 'user',
      '#default_value' => $form_state->getValue('user_to_assign') ?: NULL,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Assign'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Validate that a user is selected.
    $uid = $form_state->getValue('user_to_assign');
    if (empty($uid) || !is_numeric($uid)) {
      $form_state->setErrorByName('user_to_assign', $this->t('Please select a valid user.'));
    }

    // Check if nodes are selected in tempstore.
    $tempstore = $this->tempstorePrivate->get('reassign_user_content');
    if (empty($tempstore->get('selected_nodes'))) {
      $form_state->setErrorByName('', $this->t('No content selected for reassignment. Please select content first.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Get the user to assign from the form state and update the nodes.
    $uid = $form_state->getValue('user_to_assign');
    $tempstore = $this->tempstorePrivate->get('reassign_user_content');
    $nids = array_values($tempstore->get('selected_nodes'));
    // Update the nodes with the new user ID.
    $this->moduleHandler->loadInclude('node', 'inc', 'node.admin');
    node_mass_update($nids, ['uid' => $uid], NULL, TRUE);
    $this->messenger()->addMessage($this->t('Assigned selected content to the chosen user.'));

    try {
      $tempstore->delete('selected_nodes');
    }
    catch (TempStoreException $e) {
      $this->messenger()->addError($e->getMessage());
    }

    // Redirect to the content overview page.
    $form_state->setRedirect('view.content.page_1');
  }

}
