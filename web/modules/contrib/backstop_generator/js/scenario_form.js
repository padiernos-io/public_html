(function ($, Drupal, once) {
  Drupal.behaviors.scenarioFilter = {
    attach(context, settings) {
      once('scenario-filter', '#entity-filter-form input', context).forEach(
        function (element) {
          $(element).on('change', function () {
            const form = $(this).closest('form');

            // Get values and update the URL dynamically.
            const profileFilter = form.find('[name="profile_filter"]').val();
            const labelFilter = form.find('[name="label_filter"]').val();
            const newUrl = new URL(window.location.href);

            if (profileFilter) {
              newUrl.searchParams.set('profile_filter', profileFilter);
            } else {
              newUrl.searchParams.delete('profile_filter');
            }

            if (labelFilter) {
              newUrl.searchParams.set('label_filter', labelFilter);
            } else {
              newUrl.searchParams.delete('label_filter');
            }

            window.history.pushState(null, '', newUrl.toString());

            // Submit via AJAX.
            form.submit();
          });
        },
      );
    },
  };
})(jQuery, Drupal, once);
