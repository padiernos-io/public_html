# MINIFY JS

## Description

The Minify JS module allows a site administrator to minify all javascript files that exist in the site's code base and use those minified files on the front end of the website.

The 3.x version of the module which uses [Matthias Mullie's PHP library 'Minify'](https://github.com/matthiasmullie/minify) to minify the javascript files.

The 2.x and preview versions of the module relied on the now abandoned [JSqueeze PHP library](https://github.com/tchwork/jsqueeze).

This module also plays nicely with the [S3 Filesystem module](https://www.drupal.org/project/s3fs) and the [Google Cloud Storage Filesystem module](https://www.drupal.org/project/gcsfs).

The companion module [Minify Source HTML module](https://www.drupal.org/project/minifyhtml) minifies page source HTML.

## INSTALLATION

  1. Composer install:
  
    composer require drupal/minifyjs

  2. Enable the Minify JS module, either through the UI or via drush:

    drush en minifyjs

  3. Go to the Performance page: Configuration > Performance.

    /admin/config/development/minifyjs

  4. Click on the Manage Javascript Files tab.

    /admin/config/development/minifyjs/files

  5. Bulk minify using the checkboxes or use the Operation links for individual
     minification's. Drush is also available to manage the functionality of the
     module:

    drush scan-js
    drush minify-js
    drush minify-js-skip

  6. Go to the Performance page: Configuration > Performance. Enable the
     minified files by using the checkbox called "Use Minified Javascript
     files."

    /admin/config/development/performance

  7. Manage the settings for the site:

    /admin/config/development/minifyjs

## MAINTAINER

- Scott Joudry (slydevil) - <https://www.drupal.org/u/slydevil>
