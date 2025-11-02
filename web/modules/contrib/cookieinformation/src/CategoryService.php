<?php

namespace Drupal\cookieinformation;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Cookieinformation cookie category service.
 */
class CategoryService {

  use StringTranslationTrait;

  /**
   * Returns the cookie categories as an array.
   *
   * @return array
   *   Returns the category options.
   */
  public function getCategories(): array {
    return [
      'functional' => $this->t('Functional', [], ['context' => 'Cookie information']),
      'marketing' => $this->t('Marketing', [], ['context' => 'Cookie information']),
      'statistic' => $this->t('Statistic', [], ['context' => 'Cookie information']),
    ];
  }

  /**
   * Returns the translated label for the category.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Returns the category label.
   */
  public function getCategoryLabel(string $key): ?TranslatableMarkup {
    $categories = $this->getCategories();

    return $categories[$key] ?? NULL;
  }

}
