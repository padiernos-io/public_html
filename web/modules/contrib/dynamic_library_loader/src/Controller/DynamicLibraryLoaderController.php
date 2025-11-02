<?php

namespace Drupal\dynamic_library_loader\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DynamicLibraryLoaderController extends ControllerBase {

	/**
	 * Deletes an entry.
	 */
	public function delete($entry_id) {
	  // Load configuration.
	  $config = \Drupal::configFactory()->getEditable('dynamic_library_loader.settings');
	  $entries = $config->get('entries') ?? [];

	  // Filter out the entry to be deleted.
	  $entries = array_filter($entries, function ($entry) use ($entry_id) {
	    return $entry['id'] !== $entry_id;
	  });

	  // Save the updated configuration.
	  $config->set('entries', array_values($entries))->save();

	  \Drupal::messenger()->addMessage($this->t('Entry has been deleted.'));

	  // Redirect to the list form.
	  return $this->redirect('dynamic_library_loader.list_entries');
	}
}