(function ($, Drupal) {

  /**
   * EPT Tabs behavior.
   */
  Drupal.behaviors.eptTabs = {
    attach: function (context, settings) {
      $.each(drupalSettings.eptTabs, function(i, value){
        // Initialize jQuery UI tabs.
        var paragraphClass = drupalSettings.eptTabs[i].paragraphClass;
        if ($('.' + paragraphClass).length == 0) {
          return;
        }
        var $paragraphTabs = $('.' + paragraphClass);
        if ($paragraphTabs.hasClass('tabs-added')) {
          return;
        }
        $paragraphTabs.append('<div id="ept-tabs-' + paragraphClass + '" class="ept-container"></div>');
        $('#ept-tabs-' + paragraphClass).prepend('<ul class="tabs-' + paragraphClass + '"></ul>');

        let tabElement = '.' + paragraphClass + ' > .ept-container > .field--name-field-ept-tabs > .field__item > .paragraph--type--ept-tabs-item';
        const $elements =  $(once('reorderBlocks', tabElement));

        $elements.each(function(index) {
          $(this)
            .find('.ept-tab-title')
            .first()
            .appendTo($('.tabs-' + paragraphClass))
            .wrap('<li><a href="#ept-tabs-' + paragraphClass + '-' + index + '"></a></li>');
          $(this)
            .find('.ept-tab-content')
            .first()
            .appendTo($('#ept-tabs-' + paragraphClass))
            .wrap('<div id="ept-tabs-' + paragraphClass + '-' + index + '"></div>');
        });

        var options = {};
        drupalParagraphSettings = drupalSettings.eptTabs[i].options;
        if (drupalParagraphSettings.active != undefined && drupalParagraphSettings.active != '') {
          options['active'] = parseInt(drupalParagraphSettings.active);
        }

        if (drupalParagraphSettings.collapsible != undefined) {
          if (drupalParagraphSettings.collapsible == 1) {
            options['collapsible'] = true;
          }
          else {
            options['collapsible'] = false;
          }
        }

        if (drupalParagraphSettings.closed != undefined && drupalParagraphSettings.closed == 1) {
          options['active'] = false;
        }

        if (drupalParagraphSettings.disable != undefined) {
          if (drupalParagraphSettings.disable == 1) {
            options['disable'] = true;
          }
          else {
            options['disable'] = false;
          }
        }

        if (drupalParagraphSettings.heightStyle != undefined) {
          options['heightStyle'] = Drupal.checkPlain(drupalParagraphSettings.heightStyle);
        }

        $('#ept-tabs-' + paragraphClass).tabs(options);
        $paragraphTabs.addClass('tabs-added');
      });
    }
  };

})(jQuery, Drupal);
