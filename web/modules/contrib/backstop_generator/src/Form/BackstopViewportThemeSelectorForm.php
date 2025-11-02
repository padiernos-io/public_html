<?php

namespace Drupal\backstop_generator\Form;

use Drupal\backstop_generator\Services\ViewportGenerator;
use Drupal\breakpoint\BreakpointManager;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to select themes for viewport generation.
 */
class BackstopViewportThemeSelectorForm extends FormBase {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The breakpoint manager service.
   *
   * @var \Drupal\breakpoint\BreakpointManager
   */
  protected $breakpointManager;

  /**
   * The viewport generator service.
   *
   * @var \Drupal\backstop_generator\Services\ViewportGenerator
   */
  protected $viewportGenerator;

  /**
   * Constructs a new BackstopViewportThemeSelectorForm object.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   * @param \Drupal\breakpoint\BreakpointManager $breakpointManager
   *   The breakpoint manager service.
   * @param \Drupal\backstop_generator\Services\ViewportGenerator $viewport_generator
   *   The viewport generator service.
   */
  public function __construct(
    ThemeHandlerInterface $theme_handler,
    BreakpointManager $breakpointManager,
    ViewportGenerator $viewport_generator,
  ) {
    $this->themeHandler = $theme_handler;
    $this->breakpointManager = $breakpointManager;
    $this->viewportGenerator = $viewport_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('breakpoint.manager'),
      $container->get('backstop_generator.viewport_generator'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'backstop_theme_selection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get the list of available themes.
    $themes = $this->getAvailableThemes();
    // List the themes that have no defined breakpoints.
    $themes_description = !empty($themes['no_breakpoints']) ?
      $this->t('@themes are installed but have no breakpoints.', ['@themes' => implode(', ', $themes['no_breakpoints'])]) :
      '';

    $form['viewport_generator'] = [
      '#type' => 'details',
      '#title' => $this->t('Viewport Generator'),
      '#description' => $this->t('Generate viewports from themes with defined breakpoints.'),
      '#open' => FALSE,
    ];

    $form['viewport_generator']['themes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Themes'),
      '#options' => $themes['installed'],
      '#description' => $themes_description,
    ];

    // Add a submit button to trigger the viewport generator.
    $form['viewport_generator']['generate_viewports'] = [
      '#type' => 'submit',
      '#value' => $this->t('Generate Viewports'),
      '#submit' => ['::submitForm'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call the viewport generator service.
    $selected_themes = $form_state->getValue('themes');
    foreach ($selected_themes as $theme_id) {
      if ($theme_id !== 0) {
        $viewports = $this->viewportGenerator->createViewportsForTheme($theme_id);
        switch ($viewports) {
          case []:
            $this->messenger()->addWarning($this->t('Viewports already exist for @theme', ['@theme' => $theme_id]));
            break;

          default:
            $this->messenger()->addStatus($this->t('The viewports have been created for @theme', ['@theme' => $theme_id]));
        }
      }
    }
    // Reload the page.
    $form_state->setRedirect('<current>');
  }

  /**
   * Retrieves a list of available themes.
   *
   * @return array
   *   An associative array of available themes.
   *   - installed: Array of installed themes with defined breakpoints.
   *   - no_breakpoints: Array of installed themes with no defined breakpoints.
   */
  protected function getAvailableThemes() {
    $themes = [];

    foreach ($this->themeHandler->listInfo() as $key => $theme) {
      if (isset($theme->info['hidden']) && $theme->info['hidden'] === TRUE) {
        continue;
      }
      if (empty($this->breakpointManager->getBreakpointsByGroup($key))) {
        $themes['no_breakpoints'][] = $theme->info['name'];
        continue;
      }
      $default = $this->themeHandler->getDefault() == $key ? ' (site default)' : '';
      $themes['installed'][$key] = $theme->info['name'] . $default;
    }

    return $themes;
  }

}
