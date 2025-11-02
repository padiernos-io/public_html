<?php

namespace Drupal\module_cleanup\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Database\Connection;
use Drupal\Core\Field\FieldException;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deletes transient data.
 *
 * @package Drupal\module_cleanup\Form
 */
class TransientEntityTypeDeleteForm extends FormBase {

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
   * The private temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $privateTempStoreFactory;

  /**
   * TransientModuleDataDeleteForm constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $privateTempStoreFactory
   *   The private temp store factory.
   */
  public function __construct(
    MessengerInterface $messenger,
    Connection $database,
    PrivateTempStoreFactory $privateTempStoreFactory
  ) {
    $this->messenger = $messenger;
    $this->database = $database;
    $this->privateTempStoreFactory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('database'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'module_cleanup_transient_fields_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $errorLink = Link::fromTextAndUrl(
      $this->t('Drupal\Component\Plugin\Exception\PluginNotFoundException: The "paragraph" entity type does not exist. in Drupal\Core\Entity\EntityTypeManager->getDefinition() (line 142 of /var/www/html/core/lib/Drupal/Core/Entity/EntityTypeManager.php)'),
      Url::fromUri('https://www.drupal.org/project/paragraphs/issues/3165612#comment-14625691', ['attributes' => ['target' => '_blank']])
    )->toString();

    $fieldPurgeBatch = Link::fromTextAndUrl(
      $this->t('field_purge_batch()'),
      Url::fromUri('https://api.drupal.org/api/drupal/modules!field!field.crud.inc/function/field_purge_batch/7.x', ['attributes' => ['target' => '_blank']])
    )->toString();

    $form['module_fields'] = [
      '#type' => 'details',
      '#title' => $this->t('Run field_purge_batch(1000);'),
      '#description' => $this->t("This runs the same command as cron @fieldPurgeBatch.", ['@fieldPurgeBatch' => $fieldPurgeBatch]),
      '#open' => TRUE,
    ];

    $form['module_fields']['error_link'] = [
      '#type' => 'markup',
      '#markup' => $this->t("You may need to run this several times referencing the errors and following the prompts.<br />This will fix errors simular to @errorLink.<br />When you see 'field_purge_batch(1000) ran successfully' the errors have been fixed.", ['@errorLink' => $errorLink]),
      '#open' => TRUE,
    ];

    $tempstore = $this->privateTempStoreFactory->get('module_cleanup');

    if ($tempstore->get('part_two')) {
      $entity_type = $tempstore->get('entity_type') ? $tempstore->get('entity_type') : "";
      $form['module_fields']['entity_type'] = [
        '#type' => 'hidden',
        '#value' => $entity_type,
      ];

      $field_name = $tempstore->get('field_name') ? $tempstore->get('field_name') : "";
      $form['module_fields']['field_name'] = [
        '#type' => 'hidden',
        '#value' => $field_name,
      ];
    }

    if ($tempstore->get('part_three')) {
      $fieldStorageOptions = 'field.storage.' . $entity_type . '.' . $field_name;
      $form['module_fields']['entity_storage'] = [
        '#type' => 'hidden',
        '#value' => $fieldStorageOptions,
      ];
    }

    $form['module_fields']['actions'] = ['#type' => 'actions'];
    $form['module_fields']['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Run field_purge_batch(1000)'),
    ];

    if (!$this->tempstoreHasData()) {
      $form['module_fields']['actions']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#limit_validation_errors' => [],
        '#submit' => ['::resetForm'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $tempstore = $this->privateTempStoreFactory->get('module_cleanup');
    try {
      field_purge_batch(1000);
      if ($form_state->hasValue('entity_type') && $form_state->hasValue('field_name')) {
        $entity_type = $form_state->getValue('entity_type');
        $field_name = $form_state->getValue('field_name');
        if ($tempstore->get('part_three') && $form_state->hasValue('entity_storage') && FieldStorageConfig::loadByName($entity_type, $field_name)) {
          FieldStorageConfig::loadByName($entity_type, $field_name)->delete();
          $tempstore->set('part_two', FALSE);
          $tempstore->set('part_three', FALSE);
          $tempstore->set('field_name', "");
          $tempstore->set('entity_type', "");
        }
      }
      $this->messenger->addMessage($this->t("field_purge_batch(1000) ran successfully."));
    }
    catch (PluginNotFoundException $e) {
      $this->messenger->addError($this->t("A module left behind data when you uninstalled it. It needs to be installed to delete the data. This is the error being thrown: @error", ['@error' => $e->getMessage()]));
    }
    catch (FieldException $e) {
      if ($form_state->hasValue('entity_type') && $form_state->hasValue('field_name')) {
        $entity_type = $form_state->getValue('entity_type');
        $field_name = $form_state->getValue('field_name');
        if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
          try {
            // Create field storage.
            FieldStorageConfig::create([
              'field_name' => $field_name,
              'entity_type' => $entity_type,
              'type' => 'string',
            ])->save();

            $tempstore->set('part_three', TRUE);

            $this->messenger->addMessage($this->t("Field Storage was created for Field Name: @field_name.", ['@field_name' => $field_name]));
            $this->messenger->addMessage($this->t("This will allow field_purge_batch(1000) to run without error. Please click Run field_purge_batch(1000) again."));
          }
          catch (\LogicException $e) {
            $this->messenger->addMessage($this->t('@error Please click Run field_purge_batch(1000)', ['@error' => $e->getMessage()]));
          }
        }
      }
      else {
        $tempstore->set('part_two', TRUE);
        $error = $e->getMessage();
        $pattern = "/^Attempted to create, modify or delete an instance of field with name/";
        $pattern2 = "/when the field storage does not exist.$/";
        $pattern3 = "/ on entity type /";
        $field_name = $entity_type = '';
        $this->messenger->addError($this->t('@error', ['@error' => $e->getMessage()]));
        if (preg_match($pattern, $error) && preg_match($pattern2, $error)) {
          $parts = preg_split($pattern3, $error);
          $errorParts = explode(" ", $parts[0]);
          $field_name = end($errorParts);
          $entity_type = explode(" ", $parts[1])[0];
        }
        $tempstore->set('field_name', $field_name);
        $tempstore->set('entity_type', $entity_type);
        $this->messenger->addMessage($this->t("Field name and Entity type recorded. Please click Run field_purge_batch(1000) again."));
      }
    }
  }

  /**
   * Checks if tempstore has been set.
   *
   * @return bool
   *   True of False.
   */
  private function tempstoreHasData() {
    $tempstore = $this->privateTempStoreFactory->get('module_cleanup');
    $part_two = $tempstore->get('part_two');
    $part_three = $tempstore->get('part_three');
    $field_name = $tempstore->get('field_name');
    $entity_type = $tempstore->get('entity_type');

    if (
      $part_two ||
      $part_three ||
      $field_name != "" ||
      $entity_type != ""
    ) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Resets the filter form.
   */
  public function resetForm() {
    $tempstore = $this->privateTempStoreFactory->get('module_cleanup');
    $tempstore->set('part_two', FALSE);
    $tempstore->set('part_three', FALSE);
    $tempstore->set('field_name', '');
    $tempstore->set('entity_type', '');
  }

}
