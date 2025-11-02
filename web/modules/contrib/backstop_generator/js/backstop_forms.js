(function ($, Drupal) {
  Drupal.behaviors.backstopForms = {
    attach(context, settings) {
      const defaults =
        document.querySelector('#edit-use-defaults') ??
        document.querySelector('#edit-use-globals');
      const advancedSettings = document.querySelectorAll('.advanced_setting');

      defaults.addEventListener('change', (e) => {
        advancedSettings.forEach((field) => {
          if (!defaults.checked) {
            field.removeAttribute('readonly');
            if (
              field.hasAttribute('type') &&
              field.getAttribute('type') === 'checkbox'
            ) {
              field.removeAttribute('disabled');
              field.closest('.form-item').classList.remove('form-disabled');
            }
            return;
          }
          field.setAttribute('readonly', 'true');
          if (
            field.hasAttribute('type') &&
            field.getAttribute('type') === 'checkbox'
          ) {
            field.setAttribute('disabled', true);
            field.closest('.form-item').classList.add('form-disabled');
          }
        });
      });
    },
  };
})(jQuery, Drupal);
