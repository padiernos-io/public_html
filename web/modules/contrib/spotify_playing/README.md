# Spotify Now Playing

This module offers the ability to show your currently playing song on your
website.

1. A JSON endpoint allows you to create this functionality yourself.
   A request to `/spotify_playing` will yield a cached JSON response identical
   to Spotify's currently playing API. 
   2. Passing `reduce=1` will limit the data to only the items needed to
          render the custom block.
3. A custom block can be added to a region that will display a now playing
   widget.

This module facilitates talking to the Spotify API for you, but be warned,
as with any third party API if you abuse your access you can get cut off
without warning. Make sure to obey Spotify's terms of use regarding their APIs.

## Requirements

This module requires no modules outside of Drupal core.

## Installation (required, unless a separate INSTALL.md is provided)

Install as you would normally install a contributed Drupal module. For
further information,
see [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).

## Configuration

Enable the module at Administration > Extend.

Create a developer account at https://developer.spotify.com/.

After installing the module on your site, navigate to the settings page.

You will then create a new application on the Spotify developer site. You
will mainly use the Web API.

For the Redirect URI, that information is available on the settings page in
Drupal.

Copy the client secret and client ID from your Spotify app into Drupal and save.

The JSON endpoint should now be available at `/spotify_playing`.

You can verify token information at `/spotify_playing/status`.

The custom block will be available to place in regions.

## Maintainers

- Ian Moffitt - [nessthehero](https://www.drupal.org/u/nessthehero)
