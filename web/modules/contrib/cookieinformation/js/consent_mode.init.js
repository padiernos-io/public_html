/* global gtag, dataLayer  */
/* exported gtag, dataLayer */

/**
 * @file
 * Creates Google Consent mode default tag.
 *
 * @see https://support.cookieinformation.com/en/articles/5411279-google-consent-mode-implementation
 */

window.dataLayer = window.dataLayer || [];
function gtag(...args) {
  dataLayer.push(args);
}
gtag('consent', 'default', {
  ad_storage: 'denied',
  analytics_storage: 'denied',
  wait_for_update: 500,
});
gtag('set', 'ads_data_redaction', true);
