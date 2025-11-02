(function (Drupal) {

  /**
   * Get EPT Counter options.
   */
  function getEptCounterOptions(id) {
    if (drupalSettings.eptCounter[id] == 'undefined' ||
      drupalSettings.eptCounter[id]['options'] == 'undefined') {
      return [];
    }
    var options = drupalSettings.eptCounter[id]['options'];
    delete options['design_options'];
    delete options['pass_options_to_javascript'];
    if (options['separator'] != 'undefined') {
      options['separator'] = options['separator']
        .replace('comma', ',')
        .replace('dot', '.')
        .replace('dash', '-');
    }

    options['decimalPlaces'] = parseInt(options['decimalPlaces']);
    options['duration'] = parseInt(options['duration']);
    options['enableScrollSpy'] = options['enableScrollSpy'] ? true : false;
    options['prefix'] = Drupal.checkPlain(options['prefix']);
    options['suffix'] = Drupal.checkPlain(options['suffix']);
    options['scrollSpyDelay'] = parseInt(options['scrollSpyDelay']);
    options['scrollSpyOnce'] = options['scrollSpyOnce'] ? true : false;
    options['smartEasingAmount'] = Drupal.checkPlain(options['smartEasingAmount']);
    options['smartEasingThreshold'] = Drupal.checkPlain(options['smartEasingThreshold']);
    options['startVal'] = Drupal.checkPlain(options['startVal']);
    options['useEasing'] = options['useEasing'] ? true : false;
    options['useGrouping'] = options['useGrouping'] ? true : false;
    return drupalSettings.eptCounter[id]['options'];
  }

  /**
   * EPT Core Countup plugin.
   */
  Drupal.behaviors.eptCounter = {
    attach: function (context, settings) {
      var eptCounters = once('counter-paragraph', '.ept-paragraph-counter', context);

      eptCounters.forEach(paragraphWrapper => {
        var options = getEptCounterOptions(paragraphWrapper.id);
        const counters = once('countup', '#' + paragraphWrapper.id + ' .ept-counter-number', context);
        counters.forEach(div => {
          var numAnim = new countUp.CountUp(div.id, div.textContent, options);
          numAnim.start()
        });
      });
    }
  };

})(Drupal);



