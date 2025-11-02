/**
 * @file
 * Attaches the behaviors for the field_group_settings module.
 */

(($, Drupal, once) => {
  /**
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Adds behaviors for field_group_settings module.
   */
  Drupal.behaviors.fieldGroupSettings = {
    attach(context) {
      $(
        once(
          'field-group-settings-toggle',
          'button[data-open-next-field-group-settings]',
          context,
        ),
      ).on('click', (event) => {
        event.preventDefault();
        $(event.currentTarget)
          .next('.field-group-settings__inner')
          .toggleClass('open');
        return false;
      });
    },
  };
})(jQuery, Drupal, once);
