(function ($, Drupal, once) {
  var dKeyPressed = false;
  $(document).on('keydown', function (e) {
    if (e.key === 'd' || e.key === 'D' || e.which === 68) {
      dKeyPressed = true;
    }
  }).on('keyup', function (e) {
    if (e.key === 'd' || e.key === 'D' || e.which === 68) {
      dKeyPressed = false;
    }
  });

  Drupal.behaviors.requireDKeyForDelete = {
    attach(context, settings) {
      once(
        'requireDKeyForDelete',
        '#delete-unselected-scenarios-button',
        context,
      ).forEach(function (element) {
        $(element).on('click', function (e) {
          if (!dKeyPressed) {
            e.preventDefault();
            alert(
              "To delete all unselected scenarios, press and hold the 'D' key when clicking the 'Delete Unselected' button.",
            );
          }
        });
      });
    },
  };
})(jQuery, Drupal, once);
