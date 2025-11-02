# Extra Paragraph Types (EPT): Stats

Extra Paragraph Types: Stats module provides ability
to add Numbers with Text/Icons, Description and Title with WYSIWYG editor.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/ept_stats).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/ept_stats).


## Contents of this file

- Requirements
- Recommended modules
- Installation
- Configuration
- Troubleshooting
- SCSS compiling
- FAQ
- Maintainers


## Requirements

This module requires the following modules:
- [EPT Core](https://www.drupal.org/project/ept_core)
- [Paragraphs](https://www.drupal.org/project/paragraphs)

EPT Modules use Media module with Media Image type for background images.
Check Media Image type exists before install EPT Core module.


## Recommended modules

EPT modules provide ability to add different paragraphs in few clicks.
You can install separate paragraph types from this bunch of EPT modules:
- [EPT Accordion / FAQ](https://www.drupal.org/project/ept_accordion)
- [EPT Basic Button](https://www.drupal.org/project/ept_basic_button)
- [EPT Bootstrap Button](https://www.drupal.org/project/ept_bootstrap_button)
- [EPT Call to Action](https://www.drupal.org/project/ept_cta)
- [EPT Carousel](https://www.drupal.org/project/ept_carousel)
- [EPT Countdown](https://www.drupal.org/project/ept_countdown)
- [EPT Counter](https://www.drupal.org/project/ept_counter)
- [EPT Image](https://www.drupal.org/project/ept_image)
- [EPT Image Gallery](https://www.drupal.org/project/ept_image_gallery)
- [EPT Micromodal](https://www.drupal.org/project/ept_micromodal)
- [EPT Quote](https://www.drupal.org/project/ept_quote)
- [EPT Slick Slider](https://www.drupal.org/project/ept_slick_slider)
- [EPT Slideshow](https://www.drupal.org/project/ept_slideshow)
- [EPT Stats](https://www.drupal.org/project/ept_stats)
- [EPT Tabs](https://www.drupal.org/project/ept_tabs)
- [EPT Text](https://www.drupal.org/project/ept_text)
- [EPT Tiles](https://www.drupal.org/project/ept_tiles)
- [EPT Timeline](https://www.drupal.org/project/ept_timeline)
- [EPT Video](https://www.drupal.org/project/ept_video)
- [EPT Video and Image gallery](https://www.drupal.org/project/ept_video_and_image_gallery)
- [EPT Webform](https://www.drupal.org/project/ept_webform)
- [EPT Webform Popup](https://www.drupal.org/project/ept_webform_popup)

More about EPT paragraphs read on EPT Core module page:
[EPT Core](https://www.drupal.org/project/ept_core)

If you are looking for Extra Block Types, You can check out
this bunch of EBT modules:
- [EBT Accordion / FAQ](https://www.drupal.org/project/ebt_accordion)
- [EBT Basic Button](https://www.drupal.org/project/ebt_basic_button)
- [EBT Bootstrap Button](https://www.drupal.org/project/ebt_bootstrap_button)
- [EBT Call to Action](https://www.drupal.org/project/ebt_cta)
- [EBT Carousel](https://www.drupal.org/project/ebt_carousel)
- [EBT Countdown](https://www.drupal.org/project/ebt_countdown)
- [EBT Counter](https://www.drupal.org/project/ebt_counter)
- [EBT Image](https://www.drupal.org/project/ebt_image)
- [EBT Image Gallery](https://www.drupal.org/project/ebt_image_gallery)
- [EBT Micromodal](https://www.drupal.org/project/ebt_micromodal)
- [EBT Quote](https://www.drupal.org/project/ebt_quote)
- [EBT Slick Slider](https://www.drupal.org/project/ebt_slick_slider)
- [EBT Slideshow](https://www.drupal.org/project/ebt_slideshow)
- [EBT Stats](https://www.drupal.org/project/ebt_stats)
- [EBT Tabs](https://www.drupal.org/project/ebt_tabs)
- [EBT Text](https://www.drupal.org/project/ebt_text)
- [EBT Tiles](https://www.drupal.org/project/ebt_tiles)
- [EBT Timeline](https://www.drupal.org/project/ebt_timeline)
- [EBT Video](https://www.drupal.org/project/ebt_video)
- [EBT Video and Image gallery](https://www.drupal.org/project/ebt_video_and_image_gallery)
- [EBT Webform](https://www.drupal.org/project/ebt_webform)
- [EBT Webform Popup](https://www.drupal.org/project/ebt_webform_popup)

More about EBT blocks read on EBT Core module page:
[EBT Core](https://www.drupal.org/project/ebt_core)

If you want to enhance UI for adding Blocks from Layout builder try this module:
[Layout Builder Modal](https://www.drupal.org/project/layout_builder_modal)

If you want to enhance UI for adding Paragraphs try these modules:
[Paragraphs Edit](https://www.drupal.org/project/paragraphs_edit)
[Paragraphs Modal Edit](https://www.drupal.org/project/paragraphs_modal_edit)


## Installation

- Install as you would normally install a contributed Drupal module. Visit
  [Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules) for further information.


## Configuration

Tab page field is working only for Page content type by default.
You should add another content types on
"Tab page settings for Tab" settings form:

`/admin/structure/paragraphs_type/ept_tab/fields/paragraph.ept_tab.field_ept_tab_page`

EPT Core has configuration form with Primary/Secondary colors
and Mobile/Tablet/Desktop breakpoints in
`Administration » Configuration » Content authoring »
Extra Paragraph Types (EPT) settings`

These configs will be applied to other EPT paragraphs by default.


## Troubleshooting

- If you see the error during EPT modules installation:
  "The EPT Stats needs to be created "Image" media type.
  (Currently using Media type Image version Not created)"
  Then you need to create Image media type:
  `Structure » Media types » Add media type`


## SCSS compiling

You should install NPM and Gulp globally to compile SCSS for the module.

> npm install --global gulp-cli

Then run:

> npm install

and

> gulp watch


## FAQ

**Q: Can I use only one EPT paragraph type, for example EPT Stats,
without other modules?**

**A:** Yes, sure. EPT paragraph types tend to be standalone modules.
You can install only needed paragraph types.


## Maintainers

- [Ivan Abramenko](https://www.drupal.org/u/levmyshkin)
