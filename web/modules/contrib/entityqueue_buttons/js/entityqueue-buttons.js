(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.entityqueueButtons = {
      attach: function (context, settings) {
        once('entityqueue-buttons', '.entityqueue-button', context).forEach(function (element) {
          $(element).on('click', function () {
            // Add loading state
            $(this).addClass('is-loading').attr('disabled', 'disabled');
          });
        });

        // Handle AJAX errors globally
        $(document).ajaxError(function (event, xhr, settings, error) {
          if (settings.url.includes('/entityqueue-buttons/')) {
            $('.entityqueue-button.is-loading')
              .removeClass('is-loading')
              .removeAttr('disabled');

            Drupal.message('add', Drupal.t('An error occurred. Please try again.'), { type: 'error' });
          }
        });
      }
    };

  })(jQuery, Drupal);
