(function ($, Drupal) {

  /**
   * EPT Slideshow behavior.
   */
  Drupal.behaviors.eptSlideshow = {
    attach: function (context, settings) {
      $.each(drupalSettings.eptSlideshow, function(i, value){
        // Initialize FlexSlider.
        var paragraphClass = drupalSettings.eptSlideshow[i].paragraphClass;
        if ($('.' + paragraphClass).length == 0) {
          return;
        }
        var $paragraphSlideshow = $('.' + paragraphClass);
        if ($paragraphSlideshow.hasClass('flexslider-added')) {
          return;
        }

        var options = {};

        drupalParagraphSettings = drupalSettings.eptSlideshow[i].options;
        options['selector'] = '.slides > .slide';
        if (drupalParagraphSettings.animationSpeed != undefined && drupalParagraphSettings.animationSpeed != '') {
          options['animationSpeed'] = parseInt(drupalParagraphSettings.animationSpeed);
        }

        if (drupalParagraphSettings.animation != undefined && drupalParagraphSettings.animation != '') {
          options['animation'] = Drupal.checkPlain(drupalParagraphSettings.animation);
        }

        if (drupalParagraphSettings.direction != undefined && drupalParagraphSettings.direction != '') {
          options['direction'] = Drupal.checkPlain(drupalParagraphSettings.direction);
        }

        if (drupalParagraphSettings.reverse != undefined) {
          if (drupalParagraphSettings.reverse == 1) {
            options['reverse'] = true;
          }
          else {
            options['reverse'] = false;
          }
        }

        if (drupalParagraphSettings.animationLoop != undefined) {
          if (drupalParagraphSettings.animationLoop == 1) {
            options['animationLoop'] = true;
          }
          else {
            options['animationLoop'] = false;
          }
        }

        if (drupalParagraphSettings.smoothHeight != undefined) {
          if (drupalParagraphSettings.smoothHeight == 1) {
            options['smoothHeight'] = true;
          }
          else {
            options['smoothHeight'] = false;
          }
        }

        if (drupalParagraphSettings.startAt != undefined && drupalParagraphSettings.startAt != '') {
          options['startAt'] = Drupal.checkPlain(drupalParagraphSettings.startAt);
        }

        if (drupalParagraphSettings.slideshow != undefined) {
          if (drupalParagraphSettings.slideshow == 1) {
            options['slideshow'] = true;
          }
          else {
            options['slideshow'] = false;
          }
        }

        if (drupalParagraphSettings.animationSpeed != undefined && drupalParagraphSettings.animationSpeed != '') {
          options['animationSpeed'] = parseInt(drupalParagraphSettings.animationSpeed);
        }

        if (drupalParagraphSettings.slideshowSpeed != undefined && drupalParagraphSettings.slideshowSpeed != '') {
          options['slideshowSpeed'] = parseInt(drupalParagraphSettings.slideshowSpeed);
        }

        if (drupalParagraphSettings.initDelay != undefined && drupalParagraphSettings.initDelay != '') {
          options['initDelay'] = parseInt(drupalParagraphSettings.initDelay);
        }

        if (drupalParagraphSettings.randomize != undefined) {
          if (drupalParagraphSettings.randomize == 1) {
            options['randomize'] = true;
          }
          else {
            options['randomize'] = false;
          }
        }

        if (drupalParagraphSettings.fadeFirstSlide != undefined) {
          if (drupalParagraphSettings.fadeFirstSlide == 1) {
            options['fadeFirstSlide'] = true;
          }
          else {
            options['fadeFirstSlide'] = false;
          }
        }

        if (drupalParagraphSettings.thumbCaptions != undefined) {
          if (drupalParagraphSettings.thumbCaptions == 1) {
            options['thumbCaptions'] = true;
          }
          else {
            options['thumbCaptions'] = false;
          }
        }

        if (drupalParagraphSettings.usability.pauseOnHover != undefined) {
          if (drupalParagraphSettings.usability.pauseOnHover == 1) {
            options['pauseOnHover'] = true;
          }
          else {
            options['pauseOnHover'] = false;
          }
        }

        if (drupalParagraphSettings.usability.controlNav != undefined) {
          if (drupalParagraphSettings.usability.controlNav == 1) {
            options['controlNav'] = true;
          }
          else {
            options['controlNav'] = false;
          }
        }

        if (drupalParagraphSettings.usability.directionNav != undefined) {
          if (drupalParagraphSettings.usability.directionNav == 1) {
            options['directionNav'] = true;
          }
          else {
            options['directionNav'] = false;
          }
        }

        if (drupalParagraphSettings.usability.prevText != undefined && drupalParagraphSettings.usability.prevText != '') {
          options['prevText'] = Drupal.checkPlain(drupalParagraphSettings.usability.prevText);
        }

        if (drupalParagraphSettings.usability.nextText != undefined && drupalParagraphSettings.usability.nextText != '') {
          options['nextText'] = Drupal.checkPlain(drupalParagraphSettings.usability.nextText);
        }

        if (drupalParagraphSettings.usability.pausePlay != undefined) {
          if (drupalParagraphSettings.usability.pausePlay == 1) {
            options['pausePlay'] = true;
          }
          else {
            options['pausePlay'] = false;
          }
        }

        if (drupalParagraphSettings.usability.pauseText != undefined && drupalParagraphSettings.usability.pauseText != '') {
          options['pauseText'] = Drupal.checkPlain(drupalParagraphSettings.usability.pauseText);
        }

        if (drupalParagraphSettings.usability.playText != undefined && drupalParagraphSettings.usability.playText != '') {
          options['playText'] = Drupal.checkPlain(drupalParagraphSettings.usability.playText);
        }

        if (drupalParagraphSettings.carousel.itemWidth != undefined && drupalParagraphSettings.carousel.itemWidth != '') {
          options['itemWidth'] = parseInt(drupalParagraphSettings.carousel.itemWidth);
        }

        if (drupalParagraphSettings.carousel.itemMargin != undefined && drupalParagraphSettings.carousel.itemMargin != '') {
          options['itemMargin'] = parseInt(drupalParagraphSettings.carousel.itemMargin);
        }

        if (drupalParagraphSettings.carousel.minItems != undefined && drupalParagraphSettings.carousel.minItems != '') {
          options['minItems'] = parseInt(drupalParagraphSettings.carousel.minItems);
        }

        if (drupalParagraphSettings.carousel.maxItems != undefined && drupalParagraphSettings.carousel.maxItems != '') {
          options['maxItems'] = parseInt(drupalParagraphSettings.carousel.maxItems);
        }

        if (drupalParagraphSettings.carousel.move != undefined && drupalParagraphSettings.carousel.move != '') {
          options['move'] = parseInt(drupalParagraphSettings.carousel.move);
        }

        if (drupalParagraphSettings.carousel.allowOneSlide != undefined) {
          if (drupalParagraphSettings.carousel.allowOneSlide == 1) {
            options['allowOneSlide'] = true;
          }
          else {
            options['allowOneSlide'] = false;
          }
        }

        $paragraphSlideshow.find('.ept-slideshow-wrapper').flexslider(options);
        $paragraphSlideshow.addClass('flexslider-added');
      });
    }
  };

})(jQuery, Drupal);
