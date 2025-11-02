<?php

namespace Drupal\improve_line_breaks_filter\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\improve_line_breaks_filter\TextReplacement;

/**
 * A text filter to improve the line breaks.
 *
 * @Filter(
 *   id = "improve_line_breaks_filter",
 *   title = @Translation("Improve line breaks"),
 *   description = @Translation("Replace empty paragraphs (e.g. <code>&lt;p>&lt;/p&gt;</code> or <code>&lt;p>&amp;nbsp;&lt;/p&gt;</code>) with <code>&lt;br /&gt;</code>."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "remove_empty_paragraphs" = FALSE
 *   },
 *   weight = 50
 * )
 */
class ImproveLineBreaksFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['remove_empty_paragraphs'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove empty paragraphs'),
      '#default_value' => $this->settings['remove_empty_paragraphs'],
      '#description' => $this->t('Determines whether empty paragraphs (e.g. <code>&lt;p>&lt;/p&gt;</code> or <code>&lt;p>&amp;nbsp;&lt;/p&gt;</code>) will be deleted or replaced with <code>&lt;br /&gt;</code>.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);
    if (empty($text)) {
      return $result;
    }

    $replacement = new TextReplacement();
    $result->setProcessedText($replacement->processImproveLineBreaks($text, $this->settings['remove_empty_paragraphs']));

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t("Replace empty paragraphs (e.g. <code>&lt;p>&lt;/p&gt;</code> or <code>&lt;p>&amp;nbsp;&lt;/p&gt;</code>) with <code>&lt;br /&gt;</code>.");
  }

}
