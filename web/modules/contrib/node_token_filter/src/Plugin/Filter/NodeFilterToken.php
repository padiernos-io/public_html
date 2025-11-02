<?php

namespace Drupal\node_token_filter\Plugin\Filter;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Utility\Token;

/**
 * Wrap tabled with div with responsive class.
 */
#[Filter(
  id: "node_filter_token_by_url",
  title: new TranslatableMarkup("Find and replace Node or Group Entities tokens."),
  type: FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
  description: new TranslatableMarkup("Find and replace node or group entities tokens by current URL's entity."),
  settings: [
    "replace_empty_token" => FALSE,
  ],
)]
class NodeFilterToken extends FilterBase implements ContainerFactoryPluginInterface {

  use ContextAwarePluginTrait;

  /**
   * RouteMatch service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Drupal token service container.
   *
   * @var Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * VideoEmbedWysiwyg constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Routing\RouteMatchInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, ModuleHandlerInterface $module_handler, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('module_handler'),
      $container->get('token'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['replace_empty_token'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace empty tokens'),
      '#description' => $this->t('Remove tokens from text if they cannot be replaced with a value'),
      '#default_value' => $this->settings['replace_empty_token'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    // First check if current page is node.
    $node = $this->routeMatch->getParameter('node');
    if (!empty($node)) {
      $token_data = ['node' => $node];
    }
    else {
      // If group module is enabled and node is empty
      // then check is it group entity or not.
      if ($this->moduleHandler->moduleExists('group')) {
        $group = $this->routeMatch->getParameter('group');
        if (!empty($group)) {
          $token_data = ['group' => $group];
        }
      }
    }

    if (!empty($token_data)) {
      // Do not clear tokens from text if token not replaced.
      $token_options = ['clear' => $this->settings['replace_empty_token']];
      $text = $this->token->replace($text, $token_data, $token_options);
    }
    return new FilterProcessResult($text);
  }

}
