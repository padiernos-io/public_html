CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Requirements
 * Configuration
 * Maintainers


INTRODUCTION
------------

The text editor often replaces the line breaks with empty paragraphs
`<p>nbsp;<p>`. Paragraphs with a margin can weaken the look of your website.
This module provides a text filter that replaces empty paragraphs, for example
`<p></p>` or `<p>&nbsp;</p>` with line breaks tags `<br />`.

The module also allows you to remove empty paragraphs if you want to delete them
completely. This is not recommended because sometimes the editor needs to add an
extra space between the text.

This module skip ignore tags (script, style, code, pre, object, iframe) when it
replaces or removes unnecessary paragraphs.

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/improve_line_breaks_filter

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/improve_line_breaks_filter


INSTALLATION
------------

 * Install the Improve Line Breaks Filter module as you would normally install a
   contributed Drupal module.
   Visit https://www.drupal.org/node/1897420 for further information.


REQUIREMENTS
-------------

This module requires no modules outside of Drupal core.


CONFIGURATION
-------------

    1. Go to "Text formats and editors" on the page `/admin/config/content/formats`
    2. Add a new text format or edit an existing one
    3. In the chosen text format, select the "Improve Line Breaks Filter" option
    4. Change its order on the list (it should be at the end but before other
    filters that need to be processed afterwards)
    5. If you want to completely delete empty paragraphs set checkbox "Remove
    empty paragraphs" on the "Improve line breaks" tab in the "Filter settings"
    6. Click on the 'Save configuration' button


MAINTAINERS
-----------

 * Krzysztof Domański - https://www.drupal.org/user/3572982
