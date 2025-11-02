<?php

namespace Drupal\template_generator;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\File\FileSystem;

/**
 * Manager for the module Template Generator.
 */
class TemplateGeneratorManager {

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The field storage config storage.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;


  /**
   * Contains the system.theme configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $themeConfig;

  /**
   * Contains the file system.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $fileSystem;

  /**
   * An extension discovery instance.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected ThemeExtensionList $themeList;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Constructs a TemplateGeneratorManager.
   *
   * @param string $root
   *   The root.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   The file system.
   * @param \Drupal\Core\Extension\ThemeExtensionList $theme_list
   *   The theme extension list.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(string $root, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, EntityDisplayRepositoryInterface $entity_display_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, ConfigFactoryInterface $config, FileSystem $file_system, ThemeExtensionList $theme_list, RendererInterface $renderer) {
    $this->root = $root;
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->themeConfig = $config->get('system.theme');
    $this->fileSystem = $file_system;
    $this->themeList = $theme_list;
    $this->renderer = $renderer;
  }

  /**
   * Get the mode of generation.
   *
   * @return string
   *   The type of generation.
   */
  public function getModeGeneration() {
    $datas = $this->state->get('template_generator');
    if (isset($datas['mode'])) {
      return $datas['mode'];
    }
    else {
      return 'manual';
    }
  }

  /**
   * Test if a entity is authorized to generate template.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return bool
   *   If a entity is authorized to generate template
   */
  public function entityAuthorized($entity_type) {
    $datas = $this->state->get('template_generator');
    return isset($datas['entities_enabled'][$entity_type]) && $datas['entities_enabled'][$entity_type] !== 0;
  }

  /**
   * Generate all authorized templates.
   */
  public function generateAll() {
    $entities = $this->getEntitiesInformations();

    // Generate templates for all desired entities.
    foreach ($entities as $entity) {
      if ($this->entityAuthorized($entity['id'])) {
        foreach ($entity['bundles'] as $bundle_id => $bundle) {
          foreach ($bundle as $view_mode) {
            $this->generateTemplate($entity['id'], $bundle_id, $view_mode);
          }
        }
      }
    }
  }

  /**
   * Generate a specific template if is authorize.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle_id
   *   The bundle id.
   * @param string $view_mode
   *   The view mode.
   */
  public function generateTemplate($entity_type, $bundle_id, $view_mode) {
    $datas = $this->state->get('template_generator');
    if (isset($datas['entities_enabled'][$entity_type]) && $datas['entities_enabled'][$entity_type] !== 0) {
      $ignore = $datas['entities'][$entity_type . '_fields']['ignore'];
      if ((!isset($ignore['bundle'][$bundle_id]) or $ignore['bundle'][$bundle_id] === 0)
        && (!isset($ignore['viewmode'][$view_mode]) or $ignore['viewmode'][$view_mode] === 0)) {

        $parent_path = $this->root . '/' . $this->themeList->getPath($datas['theme'] ?? $this->themeConfig->get('default'));

        $html = NULL;
        $view_mode_tpl = str_replace('_', '-', $view_mode);
        $bundle_id_tpl = str_replace('_', '-', $bundle_id);
        $entity_type_tpl = str_replace('_', '-', $entity_type);
        if ($view_mode == 'default') {
          $end = $bundle_id_tpl;
        }
        else {
          $end = $bundle_id_tpl . '--' . $view_mode_tpl;
        }
        $path = $parent_path . '/templates/' . $entity_type_tpl;

        // Change of folder depending on the option chosen.
        switch ($datas['entities'][$entity_type . '_fields']['mode']) {
          case 'view_mode':
            $path .= '/' . $view_mode_tpl;
            break;

          case 'bundle':
            $path .= '/' . $bundle_id_tpl;
            break;

          default:
            break;
        }
        $filename = $entity_type_tpl . '--' . $end . '.html.twig';
        $full_path = $path . '/' . $filename;

        // Create file only is not exist.
        if (!file_exists($full_path)) {
          if (!file_exists($path)) {
            $this->fileSystem->mkdir($path, NULL, TRUE);
          }
        }

        // If file already exist, only change parameters.
        else {
          $html = file_get_contents($full_path);
          $val = strpos($html, "{#");
          if ($val === 0) {
            $end = strpos($html, "#}");
            if ($end !== FALSE) {
              $html = trim(substr($html, $end + 2));
            }
          }
        }
        $template = $this->template($entity_type, $bundle_id, $view_mode, $html);
        if ($template) {
          file_put_contents($full_path, $template);
        }
      }
    }
  }

  /**
   * Writing the template file.
   *
   * @param string $entity_type
   *   The name of the entity.
   * @param string $bundle
   *   The name of the bundle.
   * @param string $view_mode
   *   The name of the view mode.
   * @param string $html
   *   HTML of content.
   *
   * @return string
   *   Generated template.
   */
  public function template($entity_type, $bundle, $view_mode, $html = NULL) {

    // Get information of this view mode of bundle.
    $type = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load($entity_type . '.' . $bundle . '.' . $view_mode);

    if (!$type) {
      return NULL;
    }
    $type = $type->toArray();

    // Get all availables fields.
    $fields = '';
    $field_list = [];
    foreach ($type['content'] as $name => $content) {
      $field = $this->entityTypeManager
        ->getStorage('field_storage_config')
        ->load($entity_type . '.' . $name);
      if (!$field) {
        continue;
      }
      $field = $field->toArray();

      $field_instance = $this->entityTypeManager
        ->getStorage('field_config')
        ->load($entity_type . '.' . $bundle . '.' . $name);
      if (!$field) {
        continue;
      }
      $field_instance = $field_instance->toArray();


      // Prepare Header.
      $fields .= '* - ' . $name . PHP_EOL;
      $fields .= '* | type: ' . $field['type'] . PHP_EOL;
      $fields .= '* | cardinality: ' . $field['cardinality'] . PHP_EOL;
      $fields .= '* | required: ' . ($field_instance['required'] ? 'yes' : 'no') . PHP_EOL;
      $field_list[] = '{{ content.' . $name . ' }}';
    }

    // Return Existing Html or list of available fields.
    return '{#
/******
* @file
* Default theme implementation to display a ' . $entity_type . '
*
* Availables fields :
'
      . $fields .
      '******/
#}

' .
      ($html ?: implode(PHP_EOL, $field_list)) . "\n";
  }

  /**
   * Get information of entities like bundles or view mode.
   *
   * @return array
   *   Entities informations.
   */
  public function getEntitiesInformations() {
    $data = [];

    // Get definitions of entities.
    $entity = $this->entityDisplayRepository;
    $entity_type_definitions = $this->entityTypeManager->getDefinitions();

    // Get all content entities only.
    foreach ($entity_type_definitions as $definition) {
      if ($definition instanceof ContentEntityType) {

        // Get all bundle of this entity.
        $bundles_infos = $this->entityTypeBundleInfo->getBundleInfo($definition->id());
        $bundles = array_keys($bundles_infos);

        // Retrieval of all activated view mode for a specific bundle.
        foreach ($bundles as $bundle) {
          $vm = $entity->getViewModeOptionsByBundle($definition->id(), $bundle);
          if (count($vm) > 0) {
            $data[$definition->id()]['bundles'][$bundle] = array_keys($vm);
          }
        }

        // Set information for this entity.
        if (isset($data[$definition->id()]['bundles'])) {
          $label = $definition->getLabel();
          $data[$definition->id()]['name'] = $label->render();
          $data[$definition->id()]['id'] = $definition->id();

          // Set all avalaible bundle for this entity.
          $data[$definition->id()]['bundles_list'] = [];
          foreach ($bundles_infos as $name => $info) {
            $data[$definition->id()]['bundles_list'][$name] = (string) $info['label'];
          }

          // Set all avalaible view mode for this entity.
          $data[$definition->id()]['viewmodes_list'] = [];
          $entitydisplay = $entity->getViewModes($definition->id());

          foreach ($entitydisplay as $name => $info) {
            $data[$definition->id()]['viewmodes_list'][$name] = (string) $info['label'];
          }
        }
      }
    }
    return $data;
  }

}
