<?php

namespace Drupal\module_cleanup\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes transient data.
 *
 * @package Drupal\module_cleanup\Form
 */
class ClearUpdateDeleteForm extends FormBase {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * TransientModuleDataDeleteForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   */
  public function __construct(
    MessengerInterface $messenger,
    Connection $database,
  ) {
    $this->messenger = $messenger;
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_clear_update_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['module_data'] = [
      '#type' => 'details',
      '#title' => $this->t('Fix for: No available releases found'),
      '#markup' => "Runs this query: <pre><code>
        \$database = \Drupal::database();
        \$database
          ->delete(' key_value ')
          ->condition(' collection ', ' update_fetch_task ')
          ->execute();
      </code></pre>",
      '#open' => TRUE,
    ];

    $form['module_data']['actions'] = ['#type' => 'actions'];
    $form['module_data']['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Fix Updates'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->database->delete('key_value')->condition('collection', 'update_fetch_task')->execute();
    $this->messenger->addMessage($this->t('Avalable updates restored.'));
  }

}
