<?php

namespace Drupal\dynamic_library_loader\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form to list and manage dynamic library loader entries using config storage.
 */
class DynamicLibraryLoaderListForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynamic_library_loader_list_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Fetch entries from configuration.
    $config = \Drupal::config('dynamic_library_loader.settings');
    $entries = $config->get('entries') ?? [];

    // Sort entries alphabetically by name.
    usort($entries, fn($a, $b) => strcmp($a['name'], $b['name']));

    // Define the table structure.
    $form['entries'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Status'), // New Status column.
        $this->t('Operations'),
      ],
    ];

    foreach ($entries as $entry) {
      // Add the name column.
      $form['entries'][$entry['id']]['name'] = [
        '#markup' => $entry['name'],
      ];

      // Add the status column.
      $form['entries'][$entry['id']]['status'] = [
        '#markup' => $entry['enabled'] ? $this->t('Enabled') : $this->t('Disabled'),
      ];

      // Add the operations column with Edit/Delete links.
      $form['entries'][$entry['id']]['operations'] = [
        '#type' => 'operations',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute('dynamic_library_loader.edit_entry', ['entry_id' => $entry['id']]),
          ],
          'delete' => [
            'title' => $this->t('Delete'),
            'url' => Url::fromRoute('dynamic_library_loader.delete_entry', ['entry_id' => $entry['id']]),
          ],
        ],
      ];
    }

    // Add an "Add Entry" button at the bottom of the form.
    $form['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Entry'),
      '#submit' => ['::addEntry'],
    ];

    return $form;
  }

  /**
   * Submit handler for adding a new entry.
   */
  public function addEntry(array &$form, FormStateInterface $form_state) {
    // Generate a new unique ID for the entry.
    $entry_id = uniqid();

    // Fetch existing entries from configuration.
    $config = \Drupal::configFactory()->getEditable('dynamic_library_loader.settings');
    $entries = $config->get('entries') ?? [];

    // Add the new entry with default values.
    $entries[] = [
      'id' => $entry_id,
      'name' => $this->t('New Entry'),
      'enabled' => TRUE,
      'rows' => [],
    ];

    // Save the updated entries to configuration.
    $config->set('entries', $entries);
    $config->save();

    // Redirect to the edit form for the new entry.
    $form_state->setRedirect('dynamic_library_loader.edit_entry', ['entry_id' => $entry_id]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No default submission needed for this form.
  }
}