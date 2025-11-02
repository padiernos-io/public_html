(function ($, Drupal) {
  Drupal.behaviors.toggleScenarios = {
    attach(context, settings) {
      // Listen for changes on the master checkbox
      $('#select-all', context).change(function () {
        const isChecked = this.matches(':checked');
        $('#scenario-list-wrapper input[type="checkbox"]').prop(
          'checked',
          isChecked,
        );
      });

      // Listen for changes on individual checkboxes
      $('#scenario-list-wrapper input[type="checkbox"]', context).change(
        function () {
          const allChecked =
            $('#scenario-list-wrapper input[type="checkbox"]').length ===
            $('#scenario-list-wrapper input[type="checkbox"]:checked').length;
          $('#select-all').prop('checked', allChecked);
        },
      );

      // Initial check to set the state of the master checkbox
      const allChecked =
        $('#scenario-list-wrapper input[type="checkbox"]').length ===
        $('#scenario-list-wrapper input[type="checkbox"]:checked').length;
      $('#select-all').prop('checked', allChecked);
    },
  };
})(jQuery, Drupal);
