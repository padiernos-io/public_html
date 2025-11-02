# Custom Status Report
This module allows the customization of the Status Report page.
Hide information you don't need, or add status cards from other modules
to keep track of statuses all in one place.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/custom_status_report).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/custom_status_report).


## Requirements

This module requires no modules outside of Drupal core.

## Recommended themes

[Gin Admin Theme](https://www.drupal.org/project/gin): This module works with
the default
[Claro](https://www.drupal.org/docs/core-modules-and-themes/core-themes/claro-theme)
and [Seven](https://www.drupal.org/docs/7/core/themes/seven) themes in Drupal
core, but also works with the Gin admin theme.

## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

1. Go to Administration » Configuration » System » Custom Status Report Settings
1. Select which cards you'd like to display in the form.
1. Save the configuration. When you view the Status Report page you will see the
updated General System Information section with your selections.

## Developers: Add status card from your module
If you want to add your own status card to the Status Report page, add
the `hook_requirements()` function to your .module file like this:

```
function custom_status_report_requirements_alter(array &$requirements) {
    $module_path = \Drupal::service('extension.list.module')->getPath('custom_status_report');
    $requirements['custom_status_report'] = [
      'title' => 'Custom Status Report',
      'value' => t(' Custom Status Report installed.
        Configure what shows up here in the settings.<br>
        <a class="button button--primary button--small"
        href="/admin/config/system/custom-status-report">Settings</a>'),
      'severity' => REQUIREMENT_OK,
      'add_to_general_info' => TRUE,
      'module_icon' => '/' . $module_path . '/icons/task-list.png',
    ];
  return $requirements;
}
```
In order to have the information show in the **General System Information**
section as a card, make sure to include the `add_to_general_info => TRUE`
value to the `$requirements` array. The `module_icon` value is optional --
if you don't include it the default Drupal logo will be added automatically.


## Maintainers
- [oo0shiny](https://www.drupal.org/u/oo0shiny)
