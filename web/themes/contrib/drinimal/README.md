# Drinimal

Minimal base theme for [Drupal][2] with some opinionated changes and a few bug fixes.

## Why?

Drupal works pretty well out of the box, but it comes with  heavy div soup and many unneeded classes or attributes. It also has some serious problems with `<link>` and `<a>` markup on the front page - especially in a multilingual setup.

The default minimal theme "stable9" is too bloated. The "starterkit" theme is a child theme of "stable9" and adds more bloat. The "stark" theme is meant for testing and falls back to system templates only.

So I wrote my own base theme with minimal HTML markup and without core CSS.

I also started a [thread in the Drupal forum with some more explanations and some struggles on the way to create this minimal theme][1].

## To do / Features

* reduce div soup
  * [x] remove `dialog-off-canvas-main-canvas` wrapper div (via hook), can be reenabled via theme config
    * causes incompatibility with "announcements" and "layout_builder" core modules, which can be safely disabled - see: https://www.drupal.org/project/drupal/issues/3425710
  * [x] remove wrapper `<article`> around `<img />` from media module (via template)
    * [x] Pass alignment class from image wrapper `<article>` to img tag (via hook)
  * [x] remove wrapper divs `.layout-container`, `.layout-content` (via template)
  * see more changes in description of template files
* remove unneeded HTML attributes
  * [x] remove `data-history-node-id` attribute from `<article>` in main content
  * [x] remove `data-drupal-selector` attribute (via hook)
  * [x] remove `data-drupal-link-system-path` attr from `<a>` in menus (via hook)
  * [x] remove redundant `role` attributes from `<main>`, `<header>`, `<nav>` etc.
  * [x] remove `data-history-node-id` attribute from `<article>` (via template)
* [x] fix node `url` in `node.html.twig` if page is front page (for `rel="bookmark"` link)
* [x] disable core CSS
  * [x] reimplement `.visually-hidden` CSS (otherwise the layout breaks, also use `clip-path` instead of deprecated `clip`)
* remove wrong or useless attributes in language switch
  * [x] remove `hreflang` from `<li>`
  * [x] remove `data-drupal-link-system-path` from `<li>` and `<a>`
* fix links to front page
  * [x] in language switch
  * [x] Fix `url` template variable if node is front page (for `rel="bookmark"` link)
* [x] set `aria-current="page"` and `rel="home"` to `<a>` in menus (via hook and templates)
* [x] remove useless `<a id="main-content">`, use `<main id="main-content">` instead (via template)
* [x] remove `<meta name="Generator" />`
* [x] remove `<div data-drupal-messages-fallback class="hidden"></div>` (if not logged in)
* [ ] canonical url of home page points to `/node/{id}` --> Workaround: install `drupal/metatag` - fixed without any config, but huge dependency for that simple task
* [ ] remove unneeded `focusable` class of skip link --> would require overriding `html.html.twig` --> not worth it
* [ ] reduce empty lines in HTML markup (low priority)
* [x] disable links to untranslated pages in language switch (links to front page with `hreflang="x-default"` instead) --> might be fixed with `language_switcher_extended` module - if so, than this should be out of scope
* [x] fix scrolling to wrong position when using skip link (has position static when focused, but absolute when not --> so jumping to main content causes jumping to "top of main"+"height of skip link"
  * [ ] TODO: open issue in core

### out of scope

* [ ] redirect from `/` to `/en` if browser/client language matches --> fixed via [rljutils plugin][4]
* [ ] disable __all__ CSS/JS libs from core and contrib modules (for anonymous users) --> fixed via [disable_libraries][5] module

## Installation

install drupal and drush:

```bash
mkdir -p my-project && cd my-project
composer create-project drupal/recommended-project .
composer require drush/drush
# call `./vendor/bin/drush` instead of `drush` or
# add drush to $PATH - see https://www.drush.org/12.x/install/
drush site:install
```

install and enable theme:

```bash
composer require drupal/drinimal

drush theme:install drinimal

# set as default theme (probably unwanted, because it's a base theme)
# drush config:set -y system.theme default drinimal

# rebuild cache after setting default theme
# drush cache:rebuild
```

## Update

```bash
composer update
drush cache:rebuild
```

## Copyright and License

Copyright 2023 Raffael Jesche under the [GPL-2.0-or-later][3] license.

See `LICENSE.txt` for more information.

## Credits and third-party libraries

I copied and modified a few twig templates from Drupal core modules, which are licensed under GPL-2.0-or-later.

[1]: https://www.drupal.org/forum/support/theme-development/2023-10-16/how-to-choosecreate-a-clean-base-theme-for-drupal-10-starterkit-stable9-nonesystem
[2]: https://www.drupal.org/
[3]: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
[4]: https://codeberg.org/raffaelj/drupal-rljutils
[5]: https://www.drupal.org/project/disable_libraries
