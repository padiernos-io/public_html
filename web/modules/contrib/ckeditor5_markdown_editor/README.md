This module will install and activates the [Markdown output plugin](https://ckeditor.com/docs/ckeditor5/latest/features/markdown.html) to a CKEditor 5 instance.
This plugin lets you switch the default output from HTML to Markdown. This way you can produce lightweight texts with a simple formatting syntax that is popular among developers.

## Installation

With [Drush](https://www.drush.org/):
1. [Install Drush](https://www.drush.org/install).
2. Run `drush en ckeditor5_markdown_editor` to enable the [CKEditor5 markdown editor](https://www.drupal.org/project/ckeditor_markdown_editor) module
3. Run `drush ckeditor5_markdown_editor:install` to install ckeditor5 markdown-gfm plugin in `/libraries/ckeditor5/plugins`.
4. A new plugin setting 'Markdown down' is available for each CKEditor5 text editor at `admin/config/content/formats`.
   Enable the checkbox to let the CKEditor5 instance output Markdown formatted text instead of HTML.

## Render Markdown to HTML

Use https://www.drupal.org/project/markdown_easy to enable a text filter to convert Markdown into HTML when it's rendered on your site.
You could also use a Markdown to HTML field formatter (https://www.drupal.org/project/markdown_field_formatter) on the field in the display mode.

## Roadmap

- [x] Provide a checkbox on the editor configuration page for toggling the plugin

## Maintainers

- Sebastian Hagens - [Sebastix](https://www.drupal.org/u/sebastian-hagens)
