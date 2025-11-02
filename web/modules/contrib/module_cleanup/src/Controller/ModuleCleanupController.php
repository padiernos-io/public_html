<?php

namespace Drupal\module_cleanup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Returns responses for dblog routes.
 */
class ModuleCleanupController extends ControllerBase {

  /**
   * Constructs a DbLogController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * Displays a form for cleaning transient module data.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function overview() {
    $build['module_cleanup_transient_data'] = $this->formBuilder()->getForm('Drupal\module_cleanup\Form\TransientModuleDataDeleteForm');
    $build['module_cleanup_transient_entity_type'] = $this->formBuilder()->getForm('Drupal\module_cleanup\Form\TransientEntityTypeDeleteForm');
    $build['module_cleanup_clear_updates'] = $this->formBuilder()->getForm('Drupal\module_cleanup\Form\ClearUpdateDeleteForm');
    return $build;
  }

}
