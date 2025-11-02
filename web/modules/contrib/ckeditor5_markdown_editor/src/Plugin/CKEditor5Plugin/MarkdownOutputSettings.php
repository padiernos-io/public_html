<?php

declare(strict_types=1);

namespace Drupal\ckeditor5_markdown_editor\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Markdown Editor plugin configuration.

 */
class MarkdownOutputSettings extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface
{

  use CKEditor5PluginConfigurableTrait;


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array
  {
    return [
      'markdown_output' => false
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    return [
      'ckeditor5_markdown_editor_markdown_gfm' => $this->configuration['markdown_output'],
    ];
  }

  /**
   * {@inheritdoc}
   *
   * Form for choosing which heading tags are available.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array
  {
    $default_values = $this->configuration;

    $form['markdown_output'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Markdown output in the CKEditor5 instance'),
      '#default_value' => $default_values['markdown_output'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void
  {
    $this->configuration['markdown_output'] = $form_state->getValue('markdown_output');
  }

}
