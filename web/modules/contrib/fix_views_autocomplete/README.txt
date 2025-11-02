Fix Views Autocomplete arg must match [^/]++ Error
Module to fix "View parameter doesn't exist when editing block layout: Parameter "arg_0" must match "[^/]++"" error. 

Features
For now you're not able to use Autocomplete for filters in Views-generated blocks. If you will:
- Create View and add "block display".
- Add filters to the block, expose them to visitors and enable AJAX.
- Add page display with contextual filters and the path should have a dynamic argument such as /path/%.
- Place the block to any page then you will get error instead of your page and corresponding error message:

Symfony\Component\Routing\Exception\InvalidParameterException: Parameter "view_args" for route "views_filters.autocomplete" must match "[^/]++" ("" given) to generate a corresponding URL. in Drupal\Core\Routing\UrlGenerator->doGenerate() (line 202 of /var/www/crm.renat.t.cls-lms.com/web/core/lib/Drupal/Core/Routing/UrlGenerator.php).

This module fixes this issue.

Post-Installation
Module has no settings, affected Views just go back online after this module is enabled.

Similar projects
There is corresponding issue against Drupal core and proposed patch:
https://www.drupal.org/project/drupal/issues/3239685
However such patches are now not being accepted for many years, and it's not a best practice to patch core after each update, hence this module was written as a hassle-free fix for this issue one can use right now. If proposed patch will be accepted, this module will likely become unnecessary for newer versions of a Drupal core.