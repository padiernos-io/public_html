(function ($, Drupal, once) {
  $('#scenario-list-wrapper input[type="checkbox"]').each(function () {
    $(this).trigger('change');
  });
})(jQuery, Drupal, once);
