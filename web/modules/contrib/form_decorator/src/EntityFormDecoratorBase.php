<?php

declare(strict_types=1);

namespace Drupal\form_decorator;

use Drupal\Core\Form\FormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Provides a base class for entity form decorators.
 */
class EntityFormDecoratorBase extends FormDecoratorBase implements EntityFormInterface {

  /**
   * The inner entity form.
   *
   * @var \Drupal\Core\Entity\EntityFormInterface
   */
  protected FormInterface $inner;

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->inner->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function getOperation(): string {
    return $this->inner->getOperation();
  }

  /**
   * {@inheritdoc}
   */
  public function setOperation($operation) {
    $this->inner->setOperation($operation);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->inner->setEntity($entity);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    return $this->inner->getEntityFromRouteMatch($route_match, $entity_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    return $this->inner->buildEntity($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    return $this->inner->save($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function setStringTranslation(TranslationInterface $string_translation) {
    $this->inner->setStringTranslation($string_translation);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setModuleHandler(ModuleHandlerInterface $module_handler) {
    $this->inner->setModuleHandler($module_handler);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityTypeManager(EntityTypeManagerInterface $entity_type_manager) {
    $this->inner->setEntityTypeManager($entity_type_manager);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return $this->inner->getBaseFormId();
  }

}
