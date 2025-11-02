(function ($, Drupal) {

  /**
   * EPT Slick Slider behavior.
   */
  Drupal.behaviors.eptSlickSlider = {
    attach: function (context, settings) {
      $.each(drupalSettings.eptSlickSlider, function(i, value){
        // Initialize Slick Slider.
        var paragraphClass = drupalSettings.eptSlickSlider[i].paragraphClass;
        if ($('.' + paragraphClass).length == 0) {
          return;
        }
        var $paragraphSlider = $('.' + paragraphClass + ' .slides');
        if ($paragraphSlider.hasClass('slick-slider-added')) {
          return;
        }

        var options = {};

        drupalParagraphSettings = drupalSettings.eptSlickSlider[i].options;

        if (drupalParagraphSettings.autoWidth != undefined) {
          if (drupalParagraphSettings.autoWidth == 1) {
            options['autoWidth'] = true;
          }
          if (drupalParagraphSettings.autoWidth == 0) {
            options['autoWidth'] = false;
          }
        }

        if (drupalParagraphSettings.autoplay != undefined) {
          if (drupalParagraphSettings.autoplay == 1) {
            options['autoplay'] = true;
          }
          if (drupalParagraphSettings.autoplay == 0) {
            options['autoplay'] = false;
          }
        }

        if (drupalParagraphSettings.autoplaySpeed != undefined && drupalParagraphSettings.autoplaySpeed != '') {
          options['autoplaySpeed'] = parseInt(drupalParagraphSettings.autoplaySpeed);
        }

        if (drupalParagraphSettings.arrows != undefined) {
          if (drupalParagraphSettings.arrows == 1) {
            options['arrows'] = true;
          }
          if (drupalParagraphSettings.arrows == 0) {
            options['arrows'] = false;
          }
        }

        if (drupalParagraphSettings.centerMode != undefined) {
          if (drupalParagraphSettings.centerMode == 1) {
            options['centerMode'] = true;
          }
          if (drupalParagraphSettings.centerMode == 0) {
            options['centerMode'] = false;
          }
        }

        if (drupalParagraphSettings.centerPadding != undefined && drupalParagraphSettings.centerPadding != '') {
          options['centerPadding'] = Drupal.checkPlain(drupalParagraphSettings.centerPadding);
        }

        if (drupalParagraphSettings.dots != undefined) {
          if (drupalParagraphSettings.dots == 1) {
            options['dots'] = true;
          }
          if (drupalParagraphSettings.dots == 0) {
            options['dots'] = false;
          }
        }

        if (drupalParagraphSettings.infinite != undefined) {
          if (drupalParagraphSettings.infinite == 1) {
            options['infinite'] = true;
          }
          if (drupalParagraphSettings.infinite == 0) {
            options['infinite'] = false;
          }
        }

        if (drupalParagraphSettings.initialSlide != undefined && drupalParagraphSettings.initialSlide != '') {
          options['initialSlide'] = parseInt(drupalParagraphSettings.initialSlide);
        }

        if (drupalParagraphSettings.lazyLoad != undefined && drupalParagraphSettings.lazyLoad != '') {
          options['lazyLoad'] = Drupal.checkPlain(drupalParagraphSettings.lazyLoad);
        }

        if (drupalParagraphSettings.mobileFirst != undefined) {
          if (drupalParagraphSettings.mobileFirst == 1) {
            options['mobileFirst'] = true;
          }
          if (drupalParagraphSettings.mobileFirst == 0) {
            options['mobileFirst'] = false;
          }
        }

        if (drupalParagraphSettings.slidesToShow != undefined && drupalParagraphSettings.slidesToShow != '') {
          options['slidesToShow'] = parseInt(drupalParagraphSettings.slidesToShow);
        }

        if (drupalParagraphSettings.slidesToScroll != undefined && drupalParagraphSettings.slidesToScroll != '') {
          options['slidesToScroll'] = parseInt(drupalParagraphSettings.slidesToScroll);
        }

        if (drupalParagraphSettings.speed != undefined && drupalParagraphSettings.speed != '') {
          options['speed'] = parseInt(drupalParagraphSettings.speed);
        }

        if (drupalParagraphSettings.variableWidth != undefined) {
          if (drupalParagraphSettings.variableWidth == 1) {
            options['variableWidth'] = true;
          }
          if (drupalParagraphSettings.variableWidth == 0) {
            options['variableWidth'] = false;
          }
        }

        // Mobile.
        if (drupalParagraphSettings.responsive != undefined && drupalParagraphSettings.responsive.mobile != undefined) {
          if (drupalParagraphSettings.responsive.mobile.breakpoint != undefined && drupalParagraphSettings.responsive.mobile.breakpoint != '') {
            var mobileBreakpoint = parseInt(drupalParagraphSettings.responsive.mobile.breakpoint);
          }

          if (drupalParagraphSettings.responsive.mobile.slidesToShow != undefined && drupalParagraphSettings.responsive.mobile.slidesToShow != '') {
            var mobileSlidesToShow = parseInt(drupalParagraphSettings.responsive.mobile.slidesToShow);
          }

          if (drupalParagraphSettings.responsive.mobile.slidesToScroll != undefined && drupalParagraphSettings.responsive.mobile.slidesToScroll != '') {
            var mobileSlidesToScroll = parseInt(drupalParagraphSettings.responsive.mobile.slidesToScroll);
          }

          if (drupalParagraphSettings.responsive.mobile.centerMode != undefined) {
            if (drupalParagraphSettings.responsive.mobile.centerMode == 1) {
              var mobileCenterMode = true;
            }
            if (drupalParagraphSettings.responsive.mobile.centerMode == 0) {
              var mobileCenterMode = false;
            }
          }

          if (drupalParagraphSettings.responsive.mobile.centerPadding != undefined && drupalParagraphSettings.responsive.mobile.centerPadding != '') {
            var mobileEdgePadding = Drupal.checkPlain(drupalParagraphSettings.responsive.mobile.centerPadding);
          }
        }

        // Tablet.
        if (drupalParagraphSettings.responsive != undefined && drupalParagraphSettings.responsive.tablet != undefined) {
          if (drupalParagraphSettings.responsive.tablet.breakpoint != undefined && drupalParagraphSettings.responsive.tablet.breakpoint != '') {
            var tabletBreakpoint = parseInt(drupalParagraphSettings.responsive.tablet.breakpoint);
          }

          if (drupalParagraphSettings.responsive.tablet.slidesToShow != undefined && drupalParagraphSettings.responsive.tablet.slidesToShow != '') {
            var tabletSlidesToShow = parseInt(drupalParagraphSettings.responsive.tablet.slidesToShow);
          }

          if (drupalParagraphSettings.responsive.tablet.slidesToScroll != undefined && drupalParagraphSettings.responsive.tablet.slidesToScroll != '') {
            var tabletSlidesToScroll = parseInt(drupalParagraphSettings.responsive.tablet.slidesToScroll);
          }

          if (drupalParagraphSettings.responsive.tablet.centerMode != undefined) {
            if (drupalParagraphSettings.responsive.tablet.centerMode == 1) {
              var tabletCenterMode = true;
            }
            if (drupalParagraphSettings.responsive.tablet.centerMode == 0) {
              var tabletCenterMode = false;
            }
          }

          if (drupalParagraphSettings.responsive.tablet.centerPadding != undefined && drupalParagraphSettings.responsive.tablet.centerPadding != '') {
            var tabletEdgePadding = Drupal.checkPlain(drupalParagraphSettings.responsive.tablet.centerPadding);
          }
        }

        // Desktop.
        if (drupalParagraphSettings.responsive != undefined && drupalParagraphSettings.responsive.desktop != undefined) {
          if (drupalParagraphSettings.responsive.desktop.breakpoint != undefined && drupalParagraphSettings.responsive.desktop.breakpoint != '') {
            var desktopBreakpoint = parseInt(drupalParagraphSettings.responsive.desktop.breakpoint);
          }

          if (drupalParagraphSettings.responsive.desktop.slidesToShow != undefined && drupalParagraphSettings.responsive.desktop.slidesToShow != '') {
            var desktopSlidesToShow = parseInt(drupalParagraphSettings.responsive.desktop.slidesToShow);
          }

          if (drupalParagraphSettings.responsive.desktop.slidesToScroll != undefined && drupalParagraphSettings.responsive.desktop.slidesToScroll != '') {
            var desktopSlidesToScroll = parseInt(drupalParagraphSettings.responsive.desktop.slidesToScroll);
          }

          if (drupalParagraphSettings.responsive.desktop.centerMode != undefined) {
            if (drupalParagraphSettings.responsive.desktop.centerMode == 1) {
              var desktopCenterMode = true;
            }
            if (drupalParagraphSettings.responsive.desktop.centerMode == 0) {
              var desktopCenterMode = false;
            }
          }

          if (drupalParagraphSettings.responsive.desktop.centerPadding != undefined && drupalParagraphSettings.responsive.desktop.centerPadding != '') {
            var desktopEdgePadding = Drupal.checkPlain(drupalParagraphSettings.responsive.desktop.centerPadding);
          }
        }

        // Responsive settings.
        var responsive = [];

        if (typeof mobileBreakpoint !== 'undefined') {
          var mobileBreakpointSettings = {
            breakpoint: mobileBreakpoint,
            settings: {},
          }

          if (typeof mobileSlidesToShow !== 'undefined') {
            mobileBreakpointSettings.settings['slidesToShow'] = parseInt(mobileSlidesToShow);
          }

          if (typeof mobileSlidesToScroll !== 'undefined') {
            mobileBreakpointSettings.settings['slidesToScroll'] = parseInt(mobileSlidesToScroll);
          }

          if (typeof mobileCenterMode !== 'undefined' && mobileCenterMode != 0) {
            mobileBreakpointSettings.settings['centerMode'] = parseInt(mobileCenterMode);
          }

          if (typeof mobileСenterPadding !== 'undefined') {
            mobileBreakpointSettings.settings['centerPadding'] = Drupal.checkPlain(mobileСenterPadding);
          }

          responsive.push(mobileBreakpointSettings);
        }

        if (typeof tabletBreakpoint !== 'undefined') {
          var tabletBreakpointSettings = {
            breakpoint: tabletBreakpoint,
            settings: {},
          }

          if (typeof tabletSlidesToShow !== 'undefined') {
            tabletBreakpointSettings.settings['slidesToShow'] = parseInt(tabletSlidesToShow);
          }

          if (typeof tabletSlidesToScroll !== 'undefined') {
            tabletBreakpointSettings.settings['slidesToScroll'] = parseInt(tabletSlidesToScroll);
          }

          if (typeof tabletCenterMode !== 'undefined' && tabletCenterMode != 0) {
            tabletBreakpointSettings.settings['centerMode'] = Drupal.checkPlain(tabletCenterMode);
          }

          if (typeof tabletCenterPadding !== 'undefined') {
            tabletBreakpointSettings.settings['centerPadding'] = Drupal.checkPlain(tabletCenterPadding);
          }

          responsive.push(tabletBreakpointSettings);
        }

        if (typeof desktopBreakpoint !== 'undefined') {
          var desktopBreakpointSettings = {
            breakpoint: desktopBreakpoint,
            settings: {},
          }

          if (typeof desktopSlidesToShow !== 'undefined') {
            desktopBreakpointSettings.settings['slidesToShow'] = parseInt(desktopSlidesToShow);
          }

          if (typeof desktopSlidesToScroll !== 'undefined') {
            desktopBreakpointSettings.settings['slidesToScroll'] = parseInt(desktopSlidesToScroll);
          }

          if (typeof desktopCenterMode !== 'undefined' && desktopCenterMode != 0) {
            desktopBreakpointSettings.settings['centerMode'] = Drupal.checkPlain(desktopCenterMode);
          }

          if (typeof desktopCenterPadding !== 'undefined') {
            desktopBreakpointSettings.settings['centerPadding'] = Drupal.checkPlain(desktopCenterPadding);
          }

          responsive.push(desktopBreakpointSettings);
        }

        options['responsive'] = responsive;

        if (drupalParagraphSettings.additional.accessibility != undefined) {
          if (drupalParagraphSettings.additional.accessibility == 1) {
            options['accessibility'] = true;
          }
          if (drupalParagraphSettings.additional.accessibility == 0) {
            options['accessibility'] = false;
          }
        }

        if (drupalParagraphSettings.additional.adaptiveHeight != undefined) {
          if (drupalParagraphSettings.additional.adaptiveHeight == 1) {
            options['adaptiveHeight'] = true;
          }
          if (drupalParagraphSettings.additional.adaptiveHeight == 0) {
            options['adaptiveHeight'] = false;
          }
        }

        if (drupalParagraphSettings.additional.draggable != undefined) {
          if (drupalParagraphSettings.additional.draggable == 1) {
            options['draggable'] = true;
          }
          if (drupalParagraphSettings.additional.draggable == 0) {
            options['draggable'] = false;
          }
        }

        if (drupalParagraphSettings.additional.cssEase != undefined && drupalParagraphSettings.additional.cssEase != '') {
          if (drupalParagraphSettings.additional.cssEase == 1) {
            options['cssEase'] = true;
          }
          if (drupalParagraphSettings.additional.cssEase == 0) {
            options['cssEase'] = false;
          }
        }

        if (drupalParagraphSettings.additional.fade != undefined) {
          if (drupalParagraphSettings.additional.fade == 1) {
            options['fade'] = true;
          }
          if (drupalParagraphSettings.additional.fade == 0) {
            options['fade'] = false;
          }
        }

        if (drupalParagraphSettings.additional.focusOnSelect != undefined) {
          if (drupalParagraphSettings.additional.additional == 1) {
            options['additional'] = true;
          }
          if (drupalParagraphSettings.additional.additional == 0) {
            options['additional'] = false;
          }
        }

        if (drupalParagraphSettings.additional.easing != undefined && drupalParagraphSettings.additional.easing != '') {
          options['easing'] = Drupal.checkPlain(drupalParagraphSettings.additional.easing);
        }

        if (drupalParagraphSettings.additional.edgeFriction != undefined && drupalParagraphSettings.additional.edgeFriction != '') {
          options['edgeFriction'] = Drupal.checkPlain(drupalParagraphSettings.additional.edgeFriction);
        }

        if (drupalParagraphSettings.additional.pauseOnFocus != undefined) {
          if (drupalParagraphSettings.additional.pauseOnFocus == 1) {
            options['pauseOnFocus'] = true;
          }
          if (drupalParagraphSettings.additional.pauseOnFocus == 0) {
            options['pauseOnFocus'] = false;
          }
        }

        if (drupalParagraphSettings.additional.pauseOnHover != undefined) {
          if (drupalParagraphSettings.additional.pauseOnHover == 1) {
            options['pauseOnHover'] = true;
          }
          if (drupalParagraphSettings.additional.pauseOnHover == 0) {
            options['pauseOnHover'] = false;
          }
        }

        if (drupalParagraphSettings.additional.pauseOnDotsHover != undefined) {
          if (drupalParagraphSettings.additional.pauseOnDotsHover == 1) {
            options['pauseOnDotsHover'] = true;
          }
          if (drupalParagraphSettings.additional.pauseOnDotsHover == 0) {
            options['pauseOnDotsHover'] = false;
          }
        }

        if (drupalParagraphSettings.additional.respondTo != undefined && drupalParagraphSettings.additional.respondTo != '') {
          options['respondTo'] = Drupal.checkPlain(drupalParagraphSettings.additional.respondTo);
        }

        if (drupalParagraphSettings.additional.rows != undefined && drupalParagraphSettings.additional.rows != '') {
          options['rows'] = parseInt(drupalParagraphSettings.additional.rows);
        }

        if (drupalParagraphSettings.additional.slidesPerRow != undefined && drupalParagraphSettings.additional.slidesPerRow != '') {
          options['slidesPerRow'] = parseInt(drupalParagraphSettings.additional.slidesPerRow);
        }

        if (drupalParagraphSettings.additional.swipe != undefined) {
          if (drupalParagraphSettings.additional.swipe == 1) {
            options['swipe'] = true;
          }
          if (drupalParagraphSettings.additional.swipe == 0) {
            options['swipe'] = false;
          }
        }

        if (drupalParagraphSettings.additional.swipeToSlide != undefined) {
          if (drupalParagraphSettings.additional.swipeToSlide == 1) {
            options['swipeToSlide'] = true;
          }
          if (drupalParagraphSettings.additional.swipeToSlide == 0) {
            options['swipeToSlide'] = false;
          }
        }

        if (drupalParagraphSettings.additional.touchMove != undefined) {
          if (drupalParagraphSettings.additional.touchMove == 1) {
            options['touchMove'] = true;
          }
          if (drupalParagraphSettings.additional.touchMove == 0) {
            options['touchMove'] = false;
          }
        }

        if (drupalParagraphSettings.additional.touchThreshold != undefined && drupalParagraphSettings.additional.touchThreshold != '') {
          options['touchThreshold'] = drupalParagraphSettings.additional.touchThreshold;
        }

        if (drupalParagraphSettings.additional.useCSS != undefined) {
          if (drupalParagraphSettings.additional.useCSS == 1) {
            options['useCSS'] = true;
          }
          if (drupalParagraphSettings.additional.useCSS == 0) {
            options['useCSS'] = false;
          }
        }

        if (drupalParagraphSettings.additional.useTransform != undefined) {
          if (drupalParagraphSettings.additional.useTransform == 1) {
            options['useTransform'] = true;
          }
          if (drupalParagraphSettings.additional.useTransform == 0) {
            options['useTransform'] = false;
          }
        }

        if (drupalParagraphSettings.additional.vertical != undefined) {
          if (drupalParagraphSettings.additional.vertical == 1) {
            options['vertical'] = true;
          }
          if (drupalParagraphSettings.additional.vertical == 0) {
            options['vertical'] = false;
          }
        }

        if (drupalParagraphSettings.additional.verticalSwiping != undefined) {
          if (drupalParagraphSettings.additional.verticalSwiping == 1) {
            options['verticalSwiping'] = true;
          }
          if (drupalParagraphSettings.additional.verticalSwiping == 0) {
            options['verticalSwiping'] = false;
          }
        }

        if (drupalParagraphSettings.additional.rtl != undefined) {
          if (drupalParagraphSettings.additional.rtl == 1) {
            options['rtl'] = true;
          }
          if (drupalParagraphSettings.additional.rtl == 0) {
            options['rtl'] = false;
          }
        }

        if (drupalParagraphSettings.additional.waitForAnimate != undefined) {
          if (drupalParagraphSettings.additional.waitForAnimate == 1) {
            options['waitForAnimate'] = true;
          }
          if (drupalParagraphSettings.additional.waitForAnimate == 0) {
            options['waitForAnimate'] = false;
          }
        }

        if (drupalParagraphSettings.additional.zIndex != undefined && drupalParagraphSettings.additional.zIndex != '') {
          options['zIndex'] = Drupal.checkPlain(drupalParagraphSettings.additional.zIndex);
        }
        $paragraphSlider.slick(options);
        $paragraphSlider.addClass('slick-slider-added');
        // Fix problem with flex container for slick slider.
        // See: https://github.com/kenwheeler/slick/issues/2378
        $paragraphSlider.closest('.layout__region').css('overflow', 'hidden');
      });
    }
  };

})(jQuery, Drupal);
