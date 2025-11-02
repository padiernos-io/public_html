<?php

namespace Drupal\artisan;

/**
 * Artisan custmizations interface.
 */
interface ArtisanCustomizationsInterface {

  const PROPERTIES_DEFAULT_SELECTOR = ':root, :host, [data-theme=light]';
  const PROPERTIES_DARK_MODE_SELECTOR = ':root[data-theme="dark"], :host[data-theme="dark"], [data-theme="dark"]';

  /**
   * Active customizations.
   *
   * @return bool
   *   Active (or not) customizations.
   */
  public static function getActive(): bool;

  /**
   * Get fonts.
   *
   * @return string
   *   Font declarations styles.
   */
  public static function getFonts(): string;

  /**
   * Get properties.
   *
   * @return string
   *   Properties declarations styles.
   */
  public static function getProperties(): string;

  /**
   * Page attachments alter helper to add customizations from declarations.
   *
   * @param array $attachments
   *   Page attachments.
   */
  public static function pageAttachmentsAlter(array &$attachments): void;

  /**
   * Theme settings form alter helper.
   *
   * @param array $form
   *   Theme settings form.
   */
  public static function themeSettingsAlter(array &$form, string $theme): void;

}
