(function (Drupal, drupalSettings) {

  /**
   * EPT Countdown behavior.
   */
  Drupal.behaviors.eptCountDown = {
    attach: function (context, settings) {
      var countdowns = once('ept-countdown-paragraph', '.ept-countdown-date', context);
      countdowns.forEach(function(countdown) {
        var eptOptions = drupalSettings['eptCountdown'][countdown.getAttribute('id')];
        var countdownTimestamp = parseInt(countdown.getAttribute('data-date'));
        var countdownId = countdown.getAttribute('id');

        new FlipDown(countdownTimestamp, countdownId, {
          theme: eptOptions['options']['color_theme'],
          headings: [
            eptOptions['options']['heading_days'],
            eptOptions['options']['heading_hours'],
            eptOptions['options']['heading_minutes'],
            eptOptions['options']['heading_seconds'],
          ],
        }).start();
      });
    }
  }

})(Drupal, drupalSettings);
