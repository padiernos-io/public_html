<?php

namespace Drupal\dynamic_library_loader\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form to manage a single dynamic library loader entry.
 */
class DynamicLibraryLoaderEntryForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynamic_library_loader_entry_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entry_id = NULL) {
    if (!$entry_id) {
      throw new \InvalidArgumentException($this->t('Entry ID is required.'));
    }

    // Fetch configuration.
    $config = \Drupal::config('dynamic_library_loader.settings');
    $entries = $config->get('entries') ?? [];

    // Find the specific entry or create a new one if it doesn't exist.
    $entry = NULL;
    foreach ($entries as $e) {
      if ($e['id'] == $entry_id) {
        $entry = $e;
        break;
      }
    }
    if (!$entry) {
      $entry = [
        'id' => $entry_id,
        'name' => $this->t('New Entry'),
        'enabled' => TRUE,
        'rows' => [],
      ];
    }

    // Entry name field.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entry Name'),
      '#default_value' => $entry['name'],
      '#required' => TRUE,
    ];

    // Enabled/Disabled checkbox.
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $entry['enabled'],
      '#description' => $this->t('Uncheck to disable this entry. Disabled entries will not attach their libraries.'),
    ];

    // Help text.
    $form['examples'] = [
      '#type' => 'markup',
      '#markup' => $this->t('<p>Examples of valid contexts:</p>
        <ul>
          <li><strong>Content Types:</strong> <em>article</em></li>
          <li><strong>Taxonomy Terms:</strong> <em>tags</em></li>
          <li><strong>Paragraphs:</strong> <em>image_gallery</em></li>
          <li><strong>Views:</strong> <em>my_view:block_1</em></li>
          <li><strong>Block Types:</strong> <em>custom_block</em></li>
          <li><strong>Node ID:</strong> <em>123</em></li>
        </ul>'),
    ];

    // Initialize rows if not already set in the form state.
    if ($form_state->get('rows') === NULL) {
      $form_state->set('rows', $entry['rows']);
    }
    $rows = $form_state->get('rows');

    // Table for rows.
    $form['rows'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Context Type'),
        $this->t('Context (Machine Name or Node ID)'),
        $this->t('Theme/Module'),
        $this->t('Library'),
        $this->t('Actions'),
      ],
      '#prefix' => '<div id="rows-wrapper">',
      '#suffix' => '</div>',
    ];

    foreach ($rows as $key => $row) {
      $form['rows'][$key]['context_type'] = [
        '#type' => 'select',
        '#options' => [
          'content_type' => $this->t('Content Types'),
          'taxonomy_term' => $this->t('Taxonomy Terms'),
          'paragraph_type' => $this->t('Paragraphs'),
          'view' => $this->t('Views'),
          'block_type' => $this->t('Block Types'),
          'node_id' => $this->t('Node ID'),
        ],
        '#default_value' => $row['context_type'] ?? '',
        '#required' => TRUE,
      ];
      $form['rows'][$key]['context'] = [
        '#type' => 'textfield',
        '#default_value' => $row['context'] ?? '',
        '#required' => TRUE,
      ];
      $form['rows'][$key]['theme'] = [
        '#type' => 'textfield',
        '#default_value' => $row['theme'] ?? '',
        '#required' => TRUE,
      ];
      $form['rows'][$key]['library'] = [
        '#type' => 'textfield',
        '#default_value' => $row['library'] ?? '',
        '#required' => TRUE,
      ];
      $form['rows'][$key]['operations'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'remove_row_' . $key,
        '#submit' => ['::removeRow'],
        '#ajax' => [
          'callback' => '::ajaxRebuild',
          'wrapper' => 'rows-wrapper',
        ],
      ];
    }

    // Add row button.
    $form['add_row'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Row'),
      '#submit' => ['::addRow'],
      '#ajax' => [
        'callback' => '::ajaxRebuild',
        'wrapper' => 'rows-wrapper',
      ],
    ];

    // Hidden field for entry ID.
    $form['entry_id'] = [
      '#type' => 'hidden',
      '#value' => $entry_id,
    ];

    // Submit buttons.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save_and_continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Continue'),
      '#submit' => ['::saveAndContinue'],
    ];

    $form['actions']['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::saveAndRedirect'],
    ];

    $form['actions']['delete_entry'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Entry'),
      '#submit' => ['::deleteEntry'],
      '#attributes' => [
        'class' => ['button', 'button--danger'],
        'onclick' => 'return confirm("Are you sure you want to delete this entry?");',
      ],
    ];

    return $form;
  }

  /**
   * Save the entry and stay on the same form.
   */
  public function saveAndContinue(array &$form, FormStateInterface $form_state) {
    $this->saveEntry($form, $form_state);
    $this->messenger()->addMessage($this->t('Entry has been saved.'));
    $form_state->setRebuild(TRUE); // Stay on the same form.
  }

  /**
   * Save the entry and redirect to the list form.
   */
  public function saveAndRedirect(array &$form, FormStateInterface $form_state) {
    $this->saveEntry($form, $form_state);
    $this->messenger()->addMessage($this->t('Entry has been saved.'));
    $form_state->setRedirect('dynamic_library_loader.list_entries');
  }

  /**
   * Deletes the entry and redirects to the list form.
   */
  public function deleteEntry(array &$form, FormStateInterface $form_state) {
    $entry_id = $form_state->getValue('entry_id');

    $config = \Drupal::configFactory()->getEditable('dynamic_library_loader.settings');
    $entries = $config->get('entries') ?? [];

    $entries = array_filter($entries, fn($e) => $e['id'] != $entry_id);

    $config->set('entries', $entries);
    $config->save();

    $this->messenger()->addMessage($this->t('Entry has been deleted.'));
    $form_state->setRedirect('dynamic_library_loader.list_entries');
  }

  /**
   * Saves the entry and rows to configuration.
   */
  /**
   * Saves the entry and its rows to configuration.
   */
  /**
   * Saves the entry and its rows to configuration.
   */
  protected function saveEntry(array &$form, FormStateInterface $form_state) {
    $entry_id = $form_state->getValue('entry_id');
    $name = $form_state->getValue('name');
    $enabled = $form_state->getValue('enabled') ? 1 : 0;

    // Fetch rows from the form state.
    $rows = $form_state->getValue('rows') ?? [];
    $cleaned_rows = [];

    // Clean and validate rows.
    foreach ($rows as $row) {
      if (!empty($row['context_type']) && !empty($row['context']) && !empty($row['theme']) && !empty($row['library'])) {
        $cleaned_rows[] = [
          'context_type' => $row['context_type'],
          'context' => $row['context'],
          'theme' => $row['theme'],
          'library' => $row['library'],
        ];
      }
    }

    // Fetch existing entries from configuration.
    $config = \Drupal::configFactory()->getEditable('dynamic_library_loader.settings');
    $entries = $config->get('entries') ?? [];

    // Update or add the entry.
    $updated = FALSE;
    foreach ($entries as &$entry) {
      if ($entry['id'] === $entry_id) {
        $entry['name'] = $name;
        $entry['enabled'] = $enabled;
        $entry['rows'] = $cleaned_rows; // Update rows.
        $updated = TRUE;
        break;
      }
    }

    if (!$updated) {
      // Add a new entry if it doesn't exist.
      $entries[] = [
        'id' => $entry_id,
        'name' => $name,
        'enabled' => $enabled,
        'rows' => $cleaned_rows,
      ];
    }

    // Save the updated configuration.
    $config->set('entries', $entries);
    $config->save();

    // Add a debug message for saved data.
    \Drupal::messenger()->addMessage(t('Entry "@name" has been saved with @count rows.', [
      '@name' => $name,
      '@count' => count($cleaned_rows),
    ]));
  }

  /**
   * Adds a new row.
   */
  public function addRow(array &$form, FormStateInterface $form_state) {
    $rows = $form_state->get('rows') ?? [];
    $rows[] = [
      'context_type' => '',
      'context' => '',
      'theme' => '',
      'library' => '',
    ];
    $form_state->set('rows', $rows);
    $form_state->setRebuild(TRUE);
  }

  /**
   * Removes a row.
   */
  public function removeRow(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $row_index = explode('_', $trigger['#name'])[2];
    $rows = $form_state->get('rows');
    unset($rows[$row_index]);
    $form_state->set('rows', array_values($rows));
    $form_state->setRebuild(TRUE);
  }

  /**
   * AJAX callback for rebuilding the rows table.
   */
  public function ajaxRebuild(array &$form, FormStateInterface $form_state) {
    return $form['rows'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This method must be implemented because of FormInterface requirements.
    // Actual save logic is handled by specific submit handlers (e.g., saveAndContinue or saveAndRedirect).
  }
}