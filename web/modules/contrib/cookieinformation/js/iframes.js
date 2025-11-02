/**
 * @file
 * Hides iframes when functional cookies are not accepted.
 */

/**
 * Main function to handle iframe behavior based on cookie consent preferences.
 *
 * @function handleIframeBehavior
 * @param {jQuery} $ - jQuery instance.
 * @param {Drupal} Drupal - Drupal instance.
 * @param {function} once - Drupal's once function.
 */
function handleIframeBehavior($, Drupal, once) {
  /**
   * Drupal behavior to block iframes based on the user's cookie consent preferences.
   *
   * When functional or specified cookies are not accepted, this behavior hides iframes
   * and replaces them with a placeholder, allowing the user to change their cookie
   * consent settings.
   *
   * @type {Drupal~behavior}
   *
   * @param {Object} context - The current DOM context.
   * @param {Object} settings - Drupal settings, including Cookie Information configuration.
   */
  Drupal.behaviors.cookie_information_iframes = {
    /**
     * Attaches behavior to check and hide iframes based on cookie consent settings.
     *
     * - Checks if a specific iframe-blocking cookie category is defined in settings.
     * - Hides iframes that are not allowed by consent settings and replaces them
     *   with a consent placeholder and a button for changing settings.
     *
     * @function attach
     * @param {Object} context - The DOM context where the behavior is applied.
     * @param {Object} settings - Configuration settings, with cookie consent preferences.
     */
    attach: function attachIframeBehavior(context, settings) {
      const blockedIframesCategory = settings.cookieinformation
        .block_iframes_category
        ? `cookie_cat_${settings.cookieinformation.block_iframes_category}`
        : 'cookie_cat_functional';
      const blockedIframesCategoryName =
        settings.cookieinformation.block_iframes_category_label;

      // Function to process each iframe element
      function processIframe() {
        if (
          $(this).data('ignoreCookieBlocking') !== true &&
          $(this).attr('name') !== 'cookie-information-sharinglibrary-iframe' &&
          $(this).attr('data-consent-src') === undefined &&
          $(this).attr('data-category-consent') === undefined
        ) {
          $(this).attr('data-consent-src', $(this).attr('src'));
          $(this).attr('data-category-consent', blockedIframesCategory);
          $(this).removeAttr('src');

          $(this).after(
            `<div class='consent-placeholder' data-category='${blockedIframesCategory}'>` +
              `<div class='consent-placeholder__content'>` +
              `<h2 class='consent-placeholder__title'>${Drupal.t(
                'Unable to display content',
                {},
                { context: 'Cookie information' },
              )}</h2>` +
              `<p class='consent-placeholder__text'>${Drupal.t(
                "Unfortunately you are not able to view this content since you haven't accepted required cookie category @category.",
                { '@category': blockedIframesCategoryName },
                { context: 'Cookie information' },
              )}</p>` +
              `<button class='consent-placeholder__button' onClick='CookieConsent.renew()'>${Drupal.t(
                'Change settings',
                {},
                { context: 'Cookie information' },
              )}</button>` +
              `</div>` +
              `</div>`,
          );
        }
      }

      // Apply processing function to each iframe element
      $(once('cookieinformation-iframe', 'iframe', context)).each(
        processIframe,
      );
    },
  };
}

// Immediately invoke the handleIframeBehavior function with jQuery, Drupal, and once
handleIframeBehavior(jQuery, Drupal, once);
