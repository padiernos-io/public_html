(function ($, Drupal) {

  /**
   * EPT Carousel behavior.
   */
  Drupal.behaviors.eptCarousel = {
    attach: function (context, settings) {
      $.each(drupalSettings.eptCarousel, function(i, value){
        // Initialize FlexSlider.
        var paragraphClass = drupalSettings.eptCarousel[i].paragraphClass;
        if ($('.' + paragraphClass).length == 0) {
          return;
        }
        var $paragraphCarousel = $('.' + paragraphClass);
        if ($paragraphCarousel.hasClass('tiny-slider-added')) {
          return;
        }

        var options = {};
        options['container'] = '.' + drupalSettings.eptCarousel[i].paragraphClass + ' .ept-carousel-wrapper';
        options['prevButton'] = '.' + drupalSettings.eptCarousel[i].paragraphClass + ' .ept-carousel-controls .ept-carousel-prev';
        options['nextButton'] = '.' + drupalSettings.eptCarousel[i].paragraphClass + ' .ept-carousel-controls .ept-carousel-next';

        drupalParagraphSettings = drupalSettings.eptCarousel[i].options;
        if (drupalParagraphSettings.mode != undefined) {
          options['mode'] = Drupal.checkPlain(drupalParagraphSettings.mode);
        }

        if (drupalParagraphSettings.axis != undefined) {
          options['axis'] = Drupal.checkPlain(drupalParagraphSettings.axis);
        }

        if (drupalParagraphSettings.items != undefined && drupalParagraphSettings.items != '') {
          options['items'] = parseInt(drupalParagraphSettings.items);
        }

        if (drupalParagraphSettings.gutter != undefined && drupalParagraphSettings.gutter != '') {
          options['gutter'] = parseInt(drupalParagraphSettings.gutter);
        }

        if (drupalParagraphSettings.edgePadding != undefined && drupalParagraphSettings.edgePadding != '') {
          options['edgePadding'] = parseInt(drupalParagraphSettings.edgePadding);
        }

        if (drupalParagraphSettings.fixedWidth != undefined && drupalParagraphSettings.fixedWidth != '') {
          options['fixedWidth'] = parseInt(drupalParagraphSettings.fixedWidth);
        }

        if (drupalParagraphSettings.autoWidth != undefined) {
          if (drupalParagraphSettings.autoWidth == 1) {
            options['autoWidth'] = true;
          }
          else {
            options['autoWidth'] = false;
          }
        }

        if (drupalParagraphSettings.slideBy != undefined && drupalParagraphSettings.slideBy != '') {
          options['slideBy'] = parseInt(drupalParagraphSettings.slideBy);
        }

        if (drupalParagraphSettings.center != undefined) {
          if (drupalParagraphSettings.center == 1) {
            options['center'] = true;
          }
          else {
            options['center'] = false;
          }
        }

        if (drupalParagraphSettings.arrowKeys != undefined) {
          if (drupalParagraphSettings.arrowKeys == 1) {
            options['arrowKeys'] = true;
          }
          else {
            options['arrowKeys'] = false;
          }
        }

        if (drupalParagraphSettings.speed != undefined && drupalParagraphSettings.speed != '') {
          options['speed'] = parseInt(drupalParagraphSettings.speed);
        }

        if (drupalParagraphSettings.loop != undefined) {
          if (drupalParagraphSettings.loop == 1) {
            options['loop'] = true;
          }
          else {
            options['loop'] = false;
          }
        }

        if (drupalParagraphSettings.autoHeight != undefined) {
          if (drupalParagraphSettings.autoHeight == 1) {
            options['autoHeight'] = true;
          }
          else {
            options['autoHeight'] = false;
          }
        }

        // Additional settings.
        if (drupalParagraphSettings.additional.viewportMax != undefined && drupalParagraphSettings.additional.viewportMax != '') {
          options['viewportMax'] = parseInt(drupalParagraphSettings.additional.viewportMax);
        }

        if (drupalParagraphSettings.additional.rewind != undefined) {
          if (drupalParagraphSettings.additional.rewind == 1) {
            options['rewind'] = true;
          }
          else {
            options['rewind'] = false;
          }
        }

        if (drupalParagraphSettings.additional.touch != undefined) {
          if (drupalParagraphSettings.additional.touch == 1) {
            options['touch'] = true;
          }
          else {
            options['touch'] = false;
          }
        }

        if (drupalParagraphSettings.additional.mouseDrag != undefined) {
          if (drupalParagraphSettings.additional.mouseDrag == 1) {
            options['mouseDrag'] = true;
          }
          else {
            options['mouseDrag'] = false;
          }
        }

        if (drupalParagraphSettings.additional.swipeAngle != undefined && drupalParagraphSettings.additional.swipeAngle != '') {
          options['swipeAngle'] = parseInt(drupalParagraphSettings.additional.swipeAngle);
        }

        if (drupalParagraphSettings.additional.preventActionWhenRunning != undefined) {
          if (drupalParagraphSettings.additional.preventActionWhenRunning == 1) {
            options['preventActionWhenRunning'] = true;
          }
          else {
            options['preventActionWhenRunning'] = false;
          }
        }

        if (drupalParagraphSettings.additional.preventScrollOnTouch != undefined) {
          options['preventScrollOnTouch'] = Drupal.checkPlain(drupalParagraphSettings.additional.preventScrollOnTouch);
        }

        if (drupalParagraphSettings.additional.nested != undefined) {
          options['nested'] = Drupal.checkPlain(drupalParagraphSettings.additional.nested);
        }

        if (drupalParagraphSettings.additional.freezable != undefined) {
          if (drupalParagraphSettings.additional.freezable == 1) {
            options['freezable'] = true;
          }
          else {
            options['freezable'] = false;
          }
        }

        if (drupalParagraphSettings.additional.disable != undefined) {
          if (drupalParagraphSettings.additional.disable == 1) {
            options['disable'] = true;
          }
          else {
            options['disable'] = false;
          }
        }

        if (drupalParagraphSettings.additional.startIndex != undefined && drupalParagraphSettings.additional.startIndex != '') {
          options['startIndex'] = parseInt(drupalParagraphSettings.additional.startIndex);
        }

        if (drupalParagraphSettings.additional.useLocalStorage != undefined) {
          if (drupalParagraphSettings.additional.useLocalStorage == 1) {
            options['useLocalStorage'] = true;
          }
          else {
            options['useLocalStorage'] = false;
          }
        }

        if (drupalParagraphSettings.additional.nonce != undefined && drupalParagraphSettings.additional.nonce != '') {
          options['nonce'] = Drupal.checkPlain(drupalParagraphSettings.additional.nonce);
        }

        // Responsive settings.
        var responsive = {};

        // Mobile.
        if (drupalParagraphSettings.responsive.mobile.breakpoint != undefined && drupalParagraphSettings.responsive.mobile.breakpoint != '') {
          var mobileBreakpoint = parseInt(drupalParagraphSettings.responsive.mobile.breakpoint);
        }

        if (drupalParagraphSettings.responsive.mobile.items != undefined && drupalParagraphSettings.responsive.mobile.items != '') {
          var mobileItems = parseInt(drupalParagraphSettings.responsive.mobile.items);
        }

        if (drupalParagraphSettings.responsive.mobile.slideBy != undefined && drupalParagraphSettings.responsive.mobile.slideBy != '') {
          var mobileSlideBy = parseInt(drupalParagraphSettings.responsive.mobile.slideBy);
        }

        if (drupalParagraphSettings.responsive.mobile.gutter != undefined && drupalParagraphSettings.responsive.mobile.gutter != '') {
          var mobileGutter = parseInt(drupalParagraphSettings.responsive.mobile.gutter);
        }

        if (drupalParagraphSettings.responsive.mobile.edgePadding != undefined && drupalParagraphSettings.responsive.mobile.edgePadding != '') {
          var mobileEdgePadding = parseInt(drupalParagraphSettings.responsive.mobile.edgePadding);
        }

        if (mobileBreakpoint != undefined) {
          if (mobileItems != undefined) {
            if (responsive[mobileBreakpoint] == undefined) {
              responsive[mobileBreakpoint] = {}
            }
            responsive[mobileBreakpoint]['items'] = mobileItems;
          }

          if (mobileSlideBy != undefined) {
            if (responsive[mobileBreakpoint] == undefined) {
              responsive[mobileBreakpoint] = {}
            }
            responsive[mobileBreakpoint]['slideBy'] = mobileSlideBy;
          }

          if (mobileGutter != undefined) {
            if (responsive[mobileBreakpoint] == undefined) {
              responsive[mobileBreakpoint] = {}
            }
            responsive[mobileBreakpoint]['gutter'] = mobileGutter;
          }

          if (mobileEdgePadding != undefined) {
            if (responsive[mobileBreakpoint] == undefined) {
              responsive[mobileBreakpoint] = {}
            }
            responsive[mobileBreakpoint]['edgePadding'] = mobileEdgePadding;
          }
        }

        // Tablet.
        if (drupalParagraphSettings.responsive.tablet.breakpoint != undefined && drupalParagraphSettings.responsive.tablet.breakpoint != '') {
          var tabletBreakpoint = parseInt(drupalParagraphSettings.responsive.tablet.breakpoint);
        }

        if (drupalParagraphSettings.responsive.tablet.items != undefined && drupalParagraphSettings.responsive.tablet.items != '') {
          var tabletItems = parseInt(drupalParagraphSettings.responsive.tablet.items);
        }

        if (drupalParagraphSettings.responsive.tablet.slideBy != undefined && drupalParagraphSettings.responsive.tablet.slideBy != '') {
          var tabletSlideBy = parseInt(drupalParagraphSettings.responsive.tablet.slideBy);
        }

        if (drupalParagraphSettings.responsive.tablet.gutter != undefined && drupalParagraphSettings.responsive.tablet.gutter != '') {
          var tabletGutter = parseInt(drupalParagraphSettings.responsive.tablet.gutter);
        }

        if (drupalParagraphSettings.responsive.tablet.edgePadding != undefined && drupalParagraphSettings.responsive.tablet.edgePadding != '') {
          var tabletEdgePadding = parseInt(drupalParagraphSettings.responsive.tablet.edgePadding);
        }

        if (tabletBreakpoint != undefined) {
          if (tabletItems != undefined) {
            if (responsive[tabletBreakpoint] == undefined) {
              responsive[tabletBreakpoint] = {}
            }
            responsive[tabletBreakpoint]['items'] = tabletItems;
          }

          if (tabletSlideBy != undefined) {
            if (responsive[tabletBreakpoint] == undefined) {
              responsive[tabletBreakpoint] = {}
            }
            responsive[tabletBreakpoint]['slideBy'] = tabletSlideBy;
          }

          if (tabletGutter != undefined) {
            if (responsive[tabletBreakpoint] == undefined) {
              responsive[tabletBreakpoint] = {}
            }
            responsive[tabletBreakpoint]['gutter'] = tabletGutter;
          }

          if (tabletEdgePadding != undefined) {
            if (responsive[tabletBreakpoint] == undefined) {
              responsive[tabletBreakpoint] = {}
            }
            responsive[tabletBreakpoint]['edgePadding'] = tabletEdgePadding;
          }
        }

        // Desktop.
        if (drupalParagraphSettings.responsive.desktop.breakpoint != undefined && drupalParagraphSettings.responsive.desktop.breakpoint != '') {
          var desktopBreakpoint = parseInt(drupalParagraphSettings.responsive.desktop.breakpoint);
        }

        if (drupalParagraphSettings.responsive.desktop.items != undefined && drupalParagraphSettings.responsive.desktop.items != '') {
          var desktopItems = parseInt(drupalParagraphSettings.responsive.desktop.items);
        }

        if (drupalParagraphSettings.responsive.desktop.slideBy != undefined && drupalParagraphSettings.responsive.desktop.slideBy != '') {
          var desktopSlideBy = parseInt(drupalParagraphSettings.responsive.desktop.slideBy);
        }

        if (drupalParagraphSettings.responsive.desktop.gutter != undefined && drupalParagraphSettings.responsive.desktop.gutter != '') {
          var desktopGutter = parseInt(drupalParagraphSettings.responsive.desktop.gutter);
        }

        if (drupalParagraphSettings.responsive.desktop.edgePadding != undefined && drupalParagraphSettings.responsive.desktop.edgePadding != '') {
          var desktopEdgePadding = parseInt(drupalParagraphSettings.responsive.desktop.edgePadding);
        }

        if (desktopBreakpoint != undefined) {
          if (desktopItems != undefined) {
            if (responsive[desktopBreakpoint] == undefined) {
              responsive[desktopBreakpoint] = {}
            }
            responsive[desktopBreakpoint]['items'] = desktopItems;
          }

          if (desktopSlideBy != undefined) {
            if (responsive[desktopBreakpoint] == undefined) {
              responsive[desktopBreakpoint] = {}
            }
            responsive[desktopBreakpoint]['slideBy'] = desktopSlideBy;
          }

          if (desktopGutter != undefined) {
            if (responsive[desktopBreakpoint] == undefined) {
              responsive[desktopBreakpoint] = {}
            }
            responsive[desktopBreakpoint]['gutter'] = desktopGutter;
          }

          if (desktopEdgePadding != undefined) {
            if (responsive[desktopBreakpoint] == undefined) {
              responsive[desktopBreakpoint] = {}
            }
            responsive[desktopBreakpoint]['edgePadding'] = desktopEdgePadding;
          }
        }

        if (responsive != {}) {
          options['responsive'] = responsive;
        }

        // Controls.
        if (drupalParagraphSettings.controls.controls != undefined) {
          if (drupalParagraphSettings.controls.controls == 1) {
            options['controls'] = true;
          }
          else {
            options['controls'] = false;
          }
        }

        if (drupalParagraphSettings.controls.controlsPosition != undefined) {
          options['controlsPosition'] = Drupal.checkPlain(drupalParagraphSettings.controls.controlsPosition);
        }

        if (drupalParagraphSettings.controls.controlsTextPrev != undefined && drupalParagraphSettings.controls.controlsTextPrev != '' &&
            drupalParagraphSettings.controls.controlsTextNext != undefined && drupalParagraphSettings.controls.controlsTextNext != '') {
          options['controlsText'] = [Drupal.checkPlain(drupalParagraphSettings.controls.controlsTextPrev), Drupal.checkPlain(drupalParagraphSettings.controls.controlsTextNext)];
        }

        if (drupalParagraphSettings.controls.nav != undefined) {
          if (drupalParagraphSettings.controls.nav == 1) {
            options['nav'] = true;
          }
          else {
            options['nav'] = false;
          }
        }

        if (drupalParagraphSettings.controls.navPosition != undefined) {
          options['navPosition'] = Drupal.checkPlain(drupalParagraphSettings.controls.navPosition);
        }

        if (drupalParagraphSettings.controls.navAsThumbnails != undefined) {
          if (drupalParagraphSettings.controls.navAsThumbnails == 1) {
            options['navAsThumbnails'] = true;
          }
          else {
            options['navAsThumbnails'] = false;
          }
        }

        // Autoplay settings.
        if (drupalParagraphSettings.autoplay.autoplay != undefined) {
          if (drupalParagraphSettings.autoplay.autoplay == 1) {
            options['autoplay'] = true;
          }
          else {
            options['autoplay'] = false;
          }
        }

        if (drupalParagraphSettings.autoplay.autoplayPosition != undefined) {
          options['autoplayPosition'] = Drupal.checkPlain(drupalParagraphSettings.autoplay.autoplayPosition);
        }

        if (drupalParagraphSettings.autoplay.autoplayTimeout != undefined && drupalParagraphSettings.autoplay.autoplayTimeout != '') {
          options['autoplayTimeout'] = parseInt(drupalParagraphSettings.autoplay.autoplayTimeout);
        }

        if (drupalParagraphSettings.autoplay.autoplayDirection != undefined) {
          options['autoplayDirection'] = Drupal.checkPlain(drupalParagraphSettings.autoplay.autoplayDirection);
        }

        if (drupalParagraphSettings.autoplay.autoplayTextStart != undefined && drupalParagraphSettings.autoplay.autoplayTextStart != '' &&
            drupalParagraphSettings.autoplay.autoplayTextStop != undefined && drupalParagraphSettings.autoplay.autoplayTextStop != '') {
          options['autoplayText'] = [Drupal.checkPlain(drupalParagraphSettings.autoplay.autoplayTextStart), Drupal.checkPlain(drupalParagraphSettings.autoplay.autoplayTextStop)];
        }

        if (drupalParagraphSettings.autoplay.autoplayHoverPause != undefined) {
          if (drupalParagraphSettings.autoplay.autoplayHoverPause == 1) {
            options['autoplayHoverPause'] = true;
          }
          else {
            options['autoplayHoverPause'] = false;
          }
        }

        if (drupalParagraphSettings.autoplay.autoplayButton != undefined && drupalParagraphSettings.autoplay.autoplayButton != '') {
          options['autoplayButton'] = Drupal.checkPlain(drupalParagraphSettings.autoplay.autoplayButton);
        }

        if (drupalParagraphSettings.autoplay.autoplayButtonOutput != undefined) {
          if (drupalParagraphSettings.autoplay.autoplayButtonOutput == 1) {
            options['autoplayButtonOutput'] = true;
          }
          else {
            options['autoplayButtonOutput'] = false;
          }
        }

        if (drupalParagraphSettings.autoplay.autoplayResetOnVisibility != undefined) {
          if (drupalParagraphSettings.autoplay.autoplayResetOnVisibility == 1) {
            options['autoplayResetOnVisibility'] = true;
          }
          else {
            options['autoplayResetOnVisibility'] = false;
          }
        }

        // Animate settings.
        if (drupalParagraphSettings.animate.animateIn != undefined && drupalParagraphSettings.animate.animateIn == '') {
          options['animateIn'] = Drupal.checkPlain(drupalParagraphSettings.animate.animateIn);
        }

        if (drupalParagraphSettings.animate.animateOut != undefined && drupalParagraphSettings.animate.animateOut == '') {
          options['animateOut'] = Drupal.checkPlain(drupalParagraphSettings.animate.animateOut);
        }

        if (drupalParagraphSettings.animate.animateNormal != undefined && drupalParagraphSettings.animate.animateNormal == '') {
          options['animateNormal'] = Drupal.checkPlain(drupalParagraphSettings.animate.animateNormal);
        }

        if (drupalParagraphSettings.animate.animateDelay != undefined && drupalParagraphSettings.animate.animateDelay != '') {
          options['animateDelay'] = parseInt(drupalParagraphSettings.animate.animateDelay);
        }

        tns(options);
        $paragraphCarousel.addClass('tiny-slider-added');
      });
    }
  };

})(jQuery, Drupal);
