/* global gtag, CookieInformation */
/* exported gtag */

/**
 * @file
 * Adds event listener to follow changes in Google Consent mode.
 *
 * @see https://support.cookieinformation.com/en/articles/5411279-google-consent-mode-implementation
 *
 */

/**
 * Handles the "CookieInformationConsentGiven" event to update Google Consent Mode settings
 * based on the user's consent preferences.
 *
 * Checks if the user has given consent for analytics and marketing cookies and updates
 * the Google Consent Mode accordingly to enable or restrict certain features.
 *
 * @function handleConsentGivenEvent
 * @global
 */
function handleConsentGivenEvent() {
  if (CookieInformation.getConsentGivenFor('cookie_cat_statistic')) {
    gtag('consent', 'update', { analytics_storage: 'granted' });
  }
  if (CookieInformation.getConsentGivenFor('cookie_cat_marketing')) {
    gtag('consent', 'update', { ad_storage: 'granted' });
  }
}

window.addEventListener(
  'CookieInformationConsentGiven',
  handleConsentGivenEvent,
  false,
);
