<?php

declare(strict_types=1);

namespace Drupal\entity_display_processor\Hook;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\entity_display_processor\Element\Drilldown;
use Drupal\entity_display_processor\EntityDisplayProcessorManager;

/**
 * Additions to the field_ui form.
 */
class FieldUiForm {

  use StringTranslationTrait;

  public function __construct(
    protected readonly EntityDisplayProcessorManager $entityDisplayProcessorManager,
    TranslationInterface $translation,
  ) {
    $this->setStringTranslation($translation);
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * @see \Drupal\field_ui\Form\EntityViewDisplayEditForm
   */
  #[Hook('form_entity_view_display_edit_form_alter')]
  public function formViewDisplayEditFormAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    $entity_display = static::getEntityDisplay($form_state);
    $settings = $entity_display->getThirdPartySettings('entity_display_processor');
    $id = $settings['processor']['id'] ?? '';
    $sub_settings = ($id !== '')
      ? ($settings['processor']['settings'] ?? [])
      : [];
    $form['entity_display_processor'] = [
      '#type' => 'details',
      '#open' => $id !== '',
      '#title' => $this->t('Entity display processor'),
      '#tree' => TRUE,
    ];
    $form['entity_display_processor']['processor'] = Drilldown::createElementFromPluginManager(
      $this->t('Entity display processor plugin'),
      $this->entityDisplayProcessorManager,
      $id,
      $sub_settings,
    );
    // Prepend a submit handler, to set the values in 3rd party settings before
    // the display is saved.
    array_unshift(
      $form['actions']['submit']['#submit'],
      [static::class, 'submit'],
    );
  }

  /**
   * Custom submit handler.
   *
   * @param array $form
   *   Complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function submit(array &$form, FormStateInterface $form_state): void {
    $entity_display = static::getEntityDisplay($form_state);

    $value = $form_state->getValue(['entity_display_processor', 'processor']) ?? [];
    $id = $value['id'] ?? '';

    if ($id === '') {
      $entity_display->unsetThirdPartySetting(
        'entity_display_processor',
        'processor',
      );
      return;
    }

    $settings = ['id' => $id];
    if (!empty($value['settings'])) {
      $settings['settings'] = $value['settings'];
    }
    $entity_display->setThirdPartySetting(
      'entity_display_processor',
      'processor',
      $settings,
    );
  }

  /**
   * Gets the entity display config entity from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Entity\Display\EntityDisplayInterface
   *   The entity display config entity.
   */
  protected static function getEntityDisplay(FormStateInterface $form_state): EntityDisplayInterface {
    $entity_form = $form_state->getFormObject();
    assert($entity_form instanceof EntityFormInterface);
    $entity_display = $entity_form->getEntity();
    assert($entity_display instanceof EntityDisplayInterface);
    return $entity_display;
  }

}
