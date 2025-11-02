<?php

namespace Drupal\template_generator\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\template_generator\TemplateGeneratorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class for form generate template settings.
 */
class TemplateGeneratorForm extends FormBase {
  use StringTranslationTrait;

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Manager Template Generator.
   *
   * @var \Drupal\template_generator\TemplateGeneratorManager
   */
  protected $templateGeneratorManager;

  /**
   * Contains the system.theme configuration object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $themeConfig;

  /**
   * An extension discovery instance.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected ThemeExtensionList $themeList;

  /**
   * Constructs a CronAccessCheck.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   * @param \Drupal\template_generator\TemplateGeneratorManager $template_generator_manager
   *   The template generator manager.
   */
  public function __construct(StateInterface $state, TemplateGeneratorManager $template_generator_manager, ConfigFactoryInterface $config, ThemeExtensionList $theme_list) {
    $this->state = $state;
    $this->templateGeneratorManager = $template_generator_manager;
    $this->themeConfig = $config->get('system.theme');
    $this->themeList = $theme_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('template_generator.manager'),
      $container->get('config.factory'),
      $container->get('extension.list.theme')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'template_generator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $datas = $this->state->get('template_generator') ?: [];
    $form['#tree'] = TRUE;
    $entities_informations = $this->templateGeneratorManager->getEntitiesInformations();
    $form['mode'] = [
      '#title' => '<h2>' . $this->t('Generation mode') . '</h2>',
      '#type' => 'select',
      '#options' =>
        [
          'manual' => $this->t('Only after each save in the config page'),
          'display' => $this->t('Regenerated after each change in the display configuration'),
        ],
      '#group' => 'advanced',
      '#default_value' => $datas['mode'] ?? 'manual',
    ];
    $form['entities_enabled'] = [
      '#title' => '<h2>' . $this->t('Template entities to generate') . '</h2>',
      '#type' => 'checkboxes',
      '#options' => array_column($entities_informations, 'name', 'id'),
      '#group' => 'advanced',
      '#default_value' => $datas['entities_enabled'] ?? [],
    ];
    $form['entities'] = [
      '#type' => 'item',
      '#title' => '<h2>' . $this->t('Parameter by entity') . '</h2>',
    ];

    // List of table of each bundle of each entity.
    foreach ($entities_informations as $entity_id => $entity_infos) {
      $form['entities'][$entity_id . '_fields'] = [
        '#type' => 'item',
        '#title' => "<h3>" . $entity_infos['name'] . "</h3>",
        '#states' => [
          'visible' => [
            ':input[name="entities_enabled' . '[' . $entity_id . ']"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['entities'][$entity_id . '_fields']['mode'] = [
        '#type' => 'select',
        '#options' => [
          'none' => $this->t('No sorting'),
          'view_mode' => $this->t('Order by View Mode'),
          'bundle' => $this->t('Order by Bundle'),
        ],
        '#default_value' => $datas['entities'][$entity_id . '_fields']['mode'] ?? [],
      ];

      // Fieldset to ignore certain view mode or bundle.
      $form['entities'][$entity_id . '_fields']['ignore'] = [
        '#title' => $this->t("Ignore specific bundle or view mode"),
        '#type' => 'details',
      ];
      $form['entities'][$entity_id . '_fields']['ignore']['bundle'] = [
        '#title' => $this->t("Disabled for the following bundles :"),
        '#type' => 'checkboxes',
        '#options' => $entity_infos['bundles_list'],
        '#default_value' => $datas['entities'][$entity_id . '_fields']['ignore']['bundle'] ?? [],
      ];
      $form['entities'][$entity_id . '_fields']['ignore']['viewmode'] = [
        '#title' => $this->t("Disabled for the following view modes :"),
        '#type' => 'checkboxes',
        '#options' => $entity_infos['viewmodes_list'],
        '#default_value' => $datas['entities'][$entity_id . '_fields']['ignore']['viewmode'] ?? [],
      ];
    }
    $themes = [];
    foreach ($this->themeList->getAllInstalledInfo() as $id => $theme) {
      $themes[$id] = $theme['name'];
    }

    $form['theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Thème'),
      '#options' => $themes,
      '#default_value' => $datas['theme'] ?? $this->themeConfig->get('default'),
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and Generate'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $datas = [
      'entities_enabled' => $form_state->getValue('entities_enabled'),
      'entities' => $form_state->getValue('entities'),
      'mode' => $form_state->getValue('mode'),
      'theme' => $form_state->getValue('theme'),
    ];
    $this->state->set('template_generator', $datas);
    $this->templateGeneratorManager->generateAll();
    $this->messenger()->addMessage('Templates generated');
  }

}
