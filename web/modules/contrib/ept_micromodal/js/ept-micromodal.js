(function (Drupal) {

  /**
   * EPT Micromodal behavior.
   */
  Drupal.behaviors.eptMicromodal = {
    attach: function (context, settings) {
      var micromodals = once('ept-micromodal-paragraph-once', '.ept-micromodal-paragraph', context);
      micromodals.forEach(function(el) {
        MicroModal.init({
          disableScroll: drupalSettings['eptMicromodal'][el.getAttribute('id')]['options']['disable_scroll'],
        });
      });

    }
  };

})(Drupal);
