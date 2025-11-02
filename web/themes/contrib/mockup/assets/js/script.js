(function ($) {
  Drupal.behaviors.mockup = {
    attach: function(context, settings) {
      $(document).ready(function() {

        $(window).scroll(function() {
          var scroll = $(window).scrollTop();
          if (scroll >= 92) {
            $(".mockup-fixed-region").addClass('mockup-fixed-region-is-active');
          } else {
            $(".mockup-fixed-region").removeClass('mockup-fixed-region-is-active');
          }
        });

        if ($('.basepack-accordion-item--title-container').hasClass('is-active')) {
          $('.basepack-accordion-item--title-container.is-active').parent().addClass('is-accordion-active');
        };

        $('.basepack-accordion-item--title-container').unbind('click');
        $('.basepack-accordion-item--title-container').click(function () {
          $(this).addClass('is-active');
          $(this).parent().addClass('is-accordion-active');

          if (!(false == $(this).next().is(':visible'))) {
            $(this).removeClass('is-active');  
            $(this).parent().removeClass('is-accordion-active');
          }

          $(this).next().slideToggle(300);

          if ($(this).find('.jfu-toggle-trigger-icon').hasClass('is-active')) {
            $(this).find('.jfu-toggle-trigger-icon').removeClass('is-active');
          }
          else {                 
            $(this).find('.jfu-toggle-trigger-icon').addClass('is-active');
          }
        });

        $('.toggle-menu-mockup .navigation-toggle').unbind('click');
        $('.toggle-menu-mockup .navigation-toggle').click(function () {
          $('.region.region-navigation').toggleClass('is-active');

          if ($('.region.region-navigation').hasClass('is-active')) {
            $('.toggle-menu-mockup .navigation-toggle').attr('aria-expanded', true)
          } else {
            $('.toggle-menu-mockup .navigation-toggle').attr('aria-expanded', false)
          };

        });



        const iconSearch = $('.icon--search');
        if (iconSearch.length != 0) {
          $(once('el', iconSearch)).click(function() { 
            $('.search-block--content').toggleClass('search-active');
          });
        }   

        var $carouselGallery = $('.basepack-image-gallery--grid .basepack-image-gallery--items');
        function initSlickIfNeeded() {
          if ($(window).width() < 768) {
            if (!$carouselGallery.hasClass('slick-initialized')) {
              $carouselGallery.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                dots: false,
                arrows: false,
                adaptiveHeight: true,
                infinite: false
              });
            }
          } else {
            if ($carouselGallery.hasClass('slick-initialized')) {
              $carouselGallery.slick('unslick');
            }
          }
        }

        initSlickIfNeeded();

        $(window).on('resize', function() {
          initSlickIfNeeded();
        });

      });
    }
  }
})(jQuery);
