<?php

namespace Drupal\cookieinformation;

use Drupal\Core\Language\LanguageManagerInterface;

/**
 * The Cookieinformation language service.
 */
class LanguageService {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * A list of rewrite languages.
   *
   * @var array
   */
  protected $rewriteLanguages = [
    'no' => 'nb',
    'nn' => 'nb',
  ];

  /**
   * A list of supported languages.
   *
   * @var array
   */
  protected $supportedLanguages = [
    'ar',
    'bg',
    'ca',
    'cs',
    'da',
    'de',
    'el',
    'en',
    'es',
    'et',
    'fi',
    'fr',
    'ga',
    'he',
    'hi',
    'hr',
    'id',
    'is',
    'it',
    'ja',
    'ko',
    'lt',
    'lv',
    'ms',
    'nb',
    'nl',
    'pl',
    'pt',
    'ro',
    'ru',
    'si',
    'sk',
    'sl',
    'sq',
    'sr',
    'sv',
    'ta',
    'th',
    'tr',
    'uk',
    'vi',
    'zh',
  ];

  /**
   * Constructs a \Drupal\cookieinformation\LanguageService.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * Find current language ID.
   *
   * Find the current language ID, rewrite and validate if it is supported.
   * Fallback to 'en' if the language current language ID is not supported.
   *
   * @return string
   *   The found language ID.
   */
  public function getId() {
    $language = $this->languageManager->getCurrentLanguage()->getId();
    $language = substr($language, 0, 2);

    // Rewrite language code.
    if (isset($this->rewriteLanguages[$language])) {
      $language = $this->rewriteLanguages[$language];
    }

    return in_array($language, $this->supportedLanguages) ? $language : 'en';
  }

}
