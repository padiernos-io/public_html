(function ($, Drupal) {

  /**
   * EPT Core Colorpicker plugin.
   */
  Drupal.behaviors.eptBasicButtonForm = {
    attach: function (context, settings) {
      let colorFields = [
        'input[name*="[field_ept_settings][0][ept_settings][link_options][title_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options][background_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options][hover_title_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options][hover_background_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options2][title_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options2][background_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options2][hover_title_color]"]',
        'input[name*="[field_ept_settings][0][ept_settings][link_options2][hover_background_color]"]',
      ];

      colorFields.forEach(colorField => {
        let $elements = $(once('colorpicker', colorField, context));

        $elements.ColorPicker({
          onBeforeShow: function () {
            let color = $(colorField).val();
            if (color !== undefined && color !== '') {
              color = '#' + color.replace('#', '');
              $(this).ColorPickerSetColor(color);
            }
          },
          onShow: function (colpkr) {
            $(colpkr).fadeIn(300);
            return false;
          },
          onHide: function (colpkr) {
            $(colpkr).fadeOut(300);
            return false;
          },
          onChange: function (hsb, hex, rgb) {
            $(colorField).val('#' + hex);
          }
        });
      });
    }
  };

})(jQuery, Drupal);
