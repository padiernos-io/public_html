(function ($, Drupal) {

  /**
   * EPT Accordion behavior.
   */
  Drupal.behaviors.eptAccordion = {
    attach: function (context, settings) {
      $.each(drupalSettings.eptAccordion, function(i, value){
        // Initialize jQuery UI Accordion.
        var paragraphClass = drupalSettings.eptAccordion[i].paragraphClass;
        if ($('.' + paragraphClass).length == 0) {
          return;
        }
        var $paragraphAccordion = $('.' + paragraphClass);
        if ($paragraphAccordion.hasClass('accordion-added')) {
          return;
        }

        var options = {};
        drupalParagraphSettings = drupalSettings.eptAccordion[i].options;
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
          options['heightStyle'] = drupalParagraphSettings.heightStyle;
        }

        let all_opened = true;
        if (drupalParagraphSettings.closed_in_tablet != undefined && drupalParagraphSettings.closed_in_tablet == 1) {
          if ($(window).width() <= drupalSettings.eptCore.tabletBreakpoint) {
            all_opened = false;
          }
        }

        if (drupalParagraphSettings.closed_in_mobile != undefined && drupalParagraphSettings.closed_in_mobile == 1) {
          if ($(window).width() <= drupalSettings.eptCore.mobileBreakpoint) {
            all_opened = false;
          }
        }

        if (all_opened === false) {
          options['active'] = false;
        }

        $paragraphAccordion.find('.ept-accordion-wrapper').accordion(options);

        if (drupalParagraphSettings.opened != undefined && drupalParagraphSettings.opened == 1 && all_opened) {
          $paragraphAccordion.find('.ui-accordion-header:not(.ui-state-active)').next().slideToggle();
          $paragraphAccordion.find('.ui-icon').removeClass('ui-icon-triangle-1-e').addClass('ui-icon-triangle-1-s');
        }

        $paragraphAccordion.addClass('accordion-added');
      });
    }
  };

})(jQuery, Drupal);
