<?php

namespace Drupal\footnotes\Plugin\search_api\processor;

use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Processor\FieldsProcessorPluginBase;

/**
 * Configure whether citations added by the Footnotes module should be ignored.
 *
 * @SearchApiProcessor(
 *   id = "footnotes_ignore_citations",
 *   label = @Translation("Ignore citations"),
 *   description = @Translation("Configure whether citations added by the Footnotes module should be ignored."),
 *   stages = {
 *     "pre_index_save" = -1,
 *     "preprocess_index" = -16,
 *     "preprocess_query" = -16,
 *   }
 * )
 */
class IgnoreCitations extends FieldsProcessorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'wrapper_class' => 'footnote__citations-wrapper',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['wrapper_class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper class name'),
      '#description' => $this->t('The class name of the wrapper element for citations. Elements with this class will be ignored.'),
      '#default_value' => $this->configuration['wrapper_class'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function process(&$value) {
    $wrapperClass = $this->configuration['wrapper_class'];
    $dom = new \DOMDocument();

    // Early return if the value does not contain the wrapper class.
    if (strpos($value, $wrapperClass) === FALSE) {
      return;
    }

    // If value is empty, ignore.
    if (empty($value)) {
      return;
    }

    // Load HTML as UTF-8 and suppress any errors due to malformed HTML.
    $encodingPrefix = '<?xml encoding="UTF-8">';
    libxml_use_internal_errors(TRUE);
    $dom->loadHTML($encodingPrefix . $value, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Find and remove citation elements.
    $xpath = new \DOMXPath($dom);
    $elements = $xpath->query("//*[contains(@class, '$wrapperClass')]");
    foreach ($elements as $element) {
      $element->parentNode->removeChild($element);
    }

    // Save the modified HTML back to the value.
    $value = $dom->saveHTML();
    // Restore the original encoding.
    $value = str_replace($encodingPrefix, '', $value);
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
  }

}
