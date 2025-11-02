# EPT Hero

EPT Hero module provides ability to insert EPT Paragraph.
You can use it to create a new custom EPT paragraph type in your project.
We will be glad to adopt your custom EPT paragraph type in
EPT ecosystem on Drupal.org.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/ept_hero).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/ept_hero).


## Table of contents

- Requirements
- Recommended modules
- Installation
- Configuration
- Troubleshooting
- FAQ
- Maintainers


## Requirements

This project requires the following items:

- [EPT Core](https://www.drupal.org/project/ept_core)
- [Paragraphs](https://www.drupal.org/project/paragraphs)

EPT Modules use Media module with Media Image type for background images.
Check Media Image type exists before install EPT Core module.


## Recommended modules

EPT modules provide ability to add different paragraphs in few clicks.
You can install separate paragraph types from this bunch of EPT modules:
- [EPT Accordion / FAQ](https://www.drupal.org/project/ept_accordion)
- [EPT Basic Button](https://www.drupal.org/project/ept_basic_button)
- [EPT Block](https://www.drupal.org/project/ept_block)
- [EPT Bootstrap Button](https://www.drupal.org/project/ept_bootstrap_button)
- [EPT Call to Action](https://www.drupal.org/project/ept_cta)
- [EPT Carousel](https://www.drupal.org/project/ept_carousel)
- [EPT Columns](https://www.drupal.org/project/ept_columns)
- [EPT Countdown](https://www.drupal.org/project/ept_countdown)
- [EPT Counter](https://www.drupal.org/project/ept_counter)
- [EPT Hero](https://www.drupal.org/project/ept_hero)
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
- [EPT Views](https://www.drupal.org/project/ept_views)
- [EPT Webform](https://www.drupal.org/project/ept_webform)
- [EPT Webform Popup](https://www.drupal.org/project/ept_webform_popup)

More about EPT paragraphs read on EPT Core module page:
[EPT Core](https://www.drupal.org/project/ept_core)

If you are looking for Extra Block Types, You can check out
this bunch of EBT modules:
- [EBT Accordion / FAQ](https://www.drupal.org/project/ebt_accordion)
- [EBT Basic Button](https://www.drupal.org/project/ebt_basic_button)
- [EBT Block](https://www.drupal.org/project/ebt_block)
- [EBT Bootstrap Button](https://www.drupal.org/project/ebt_bootstrap_button)
- [EBT Call to Action](https://www.drupal.org/project/ebt_cta)
- [EBT Carousel](https://www.drupal.org/project/ebt_carousel)
- [EBT Columns](https://www.drupal.org/project/ebt_columns)
- [EBT Countdown](https://www.drupal.org/project/ebt_countdown)
- [EBT Counter](https://www.drupal.org/project/ebt_counter)
- [EBT Hero](https://www.drupal.org/project/ebt_hero)
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
- [EBT Views](https://www.drupal.org/project/ebt_views)
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

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration


After enable this project will be available a new paragraph type called `EPT Text`

You can configure there:
- Margin;
- Border;
- Padding;
- Border Color;
- Border Style;
- Border Radius;
- Background Color;
- Background Image Style;
- Edge to Edge;
- Container Max Width.

EPT Core has configuration form with Primary/Secondary colors and
Mobile/Tablet/Desktop breakpoints in:
Administration » Configuration » Content authoring » Extra Block Types (EPT)
settings

These configs will be applied to other EPT paragraphs by default.

## Troubleshooting

- If you see the error during EPT modules installation: "The EPT Text needs
  to be created "Image" media type. (Currently using Media type Image version
  Not created)"
  Then you need to create Image media type:
  Structure » Media types » Add media type


## FAQ

**Q: Can I use only one EPT paragraph type, for example EPT Slideshow, without other modules?**

**A:** Yes, sure. EPT paragraph types tend to be standalone modules. You can install
only needed paragraph types.


## Maintainers

- Ivan Abramenko - [levmyshkin](https://www.drupal.org/u/levmyshkin)

