# Twig Placeholders

## Overview

Twig Placeholders provides Twig functions for generating placeholder content in your templates. This includes dynamically generated Lorem Ipsum text and placeholder images, making it useful for prototyping, development, and theming and is especially powerful when demonstrating Drupal single directory components in component libraries.

## Features

- **Generate Lorem Ipsum text** using a simple Twig function.
- **Generate placeholder images** from [Lorem Picsum](https://picsum.photos/) with customisable attributes.
- **Generate placeholder logos** from [Logo Ipsum](https://logoipsum.com/) with customisable attributes.
- **Generate placeholder videos** from [Test Videos](https://test-videos.co.uk) with customisable attributes.
- **Generate placeholder menu data** to loop through or pass to your component.
- **Generate placeholder `<select>` data** to loop through or pass to your component.
- **Generate placeholder `<table>` data** to loop through or pass to your component.
- **Fully configurable** via Twig function arguments.
- **Works seamlessly** with Drupal 10.3 and above.

## Usage
**SKIP TO:** [Lorem Ipsum](#generating-placeholder-lorem-ipsum-text) | [Images](#generating-placeholder-images) | [Videos](#generating-placeholder-videos) | [Menu data](#generating-placeholder-menu-data) | [Select data](#generating-placeholder-select-data) | [Table data](#generating-placeholder-table-data)

### Generating Placeholder Lorem Ipsum Text

The `tp_lorem_ipsum` function generates Lorem Ipsum text with a specified word count.

```twig
<p>{{ tp_lorem_ipsum() }}</p>
```

**Arguments:**
| Parameter    | Type | Default | Description                                                                                             |
|--------------|------|---------|---------------------------------------------------------------------------------------------------------|
| `word_count` | int  | `20`    | The total number of words that you want to generate. Returns a markup object with no HTML tags.         |
| `para_count` | int  | `null`  | The number of paragraphs `<p>`s you want to wrap your words in. Returns a markup object with HTML tags. |
| `punctuate`. | bool | `true`  | Whether to add punctuation (full stops / periods `.`) to the sentences.                                 |

#### **Examples:**

**Generate 20 words of lorem ipsum:**
```twig
{{ tp_lorem_ipsum() }}
```
_Output:_
```html
Porttitor orci felis blandit quis laoreet ad phasellus sed aliquam duis interdum. Praesent ornare suspendisse id eros integer amet fringilla.
```

**Generate 3 words of lorem ipsum with no punctuation:**
```twig
{{ tp_lorem_ipsum(3, null, false) }}
```
_Output:_
```html
Tincidunt iaculis netus
```

**Generate 100 words of lorem ipsum across 3 paragraphs:**
```twig
{{ tp_lorem_ipsum(100, 3) }}
```
_Output:_
```html
<p>Sem ac taciti felis feugiat lacinia sagittis fames. Lacus nibh eros integer eleifend nostra nisl praesent quis urna. Congue ut a metus suscipit inceptos magna faucibus elementum cubilia phasellus eu. Eget conubia nulla blandit dictum quam commodo torquent class pellentesque lorem nam.</p>
<p>Nunc amet porttitor pharetra luctus ligula convallis sodales habitasse. Donec rhoncus cursus dolor senectus rutrum pretium fusce in. Platea vel id potenti enim maecenas proin mauris et. Iaculis vulputate massa tincidunt morbi sit varius dapibus lectus.</p>
<p>Mollis laoreet porta tortor velit bibendum mi justo curae. Mattis aliquet adipiscing libero leo erat fringilla vitae tristique ullamcorper euismod litora. Tellus.</p>
```

### Generating Placeholder Images

The `tp_image` function generates a placeholder image URL or an entire `<img>` tag with customisable attributes.

```twig
{{ tp_image() }}
```

#### **Optional Arguments:**
| Parameter   | Type   | Default | Description                                                                                |
|-------------|--------|---------|--------------------------------------------------------------------------------------------|
| `url_only`  | bool   | `false` | If `true`, returns only the image URL instead of an `<img>` tag.                           |
| `width`     | int    | `800`   | Width of the image (px).                                                                   |
| `height`    | int    | `450`   | Height of the image (px).                                                                  |
| `image_id`  | int    | `null`  | Specific image ID from Picsum Photos. See [Lorem Picsum IDs](https://picsum.photos/images) |
| `grayscale` | bool   | `false` | Apply grayscale filter if `true`.                                                          |
| `blur`      | int    | `null`  | Blur level (1-10).                                                                         |
| `extension` | string | `null`  | Image format (`webp` or `jpg`).                                                            |

#### **Examples:**

**Generate a default placeholder image:**
```twig
{{ tp_image() }}
```
_Output:_
```html
<img src="https://picsum.photos/800/450" alt="Placeholder image" width="800" height="450">
```

**Generate only the image URL:**
```twig
{{ tp_image(true) }}
```
_Output:_
```text
https://picsum.photos/800/450
```

**Generate a grayscale image:**
```twig
{{ tp_image(false, 600, 400, null, true) }}
```
_Output:_
```html
<img src="https://picsum.photos/600/400?grayscale" alt="Placeholder image" width="600" height="400">
```

**Generate a blurred image (level 5):**
```twig
{{ tp_image(false, 500, 300, null, false, 5) }}
```
_Output:_
```html
<img src="https://picsum.photos/500/300?blur=5" alt="Placeholder image" width="500" height="300">
```

### Generating Placeholder Logos

The `tp_logo` function generates a placeholder SVG logo.

```twig
{{ tp_logo() }}
```

#### **Optional Arguments:**
| Parameter        | Type       | Default | Description                                                                                                                                           |
|------------------|------------|---------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
| `category_or_id` | string/int | `null`  | Either a specific category of logo from ['badge', 'gram', 'type'].<br>Or a specific logo id. There are 16 logos in total sequential ids from 0 to 15. |

#### **Examples:**

**Generate a default placeholder logo:**
```twig
{{ tp_logo() }}
```
_Output:_
```html
<svg>...</svg>>
```

### Generating Placeholder Videos
The `tp_video` function generates a placeholder video URL or an entire `<video>` tag with customisable attributes.

```twig
{{ tp_video() }}
```

#### **Optional Arguments:**
| Parameter           | Type   | Default | Description                                                                              |
|---------------------|--------|---------|------------------------------------------------------------------------------------------|
| `url_only`          | bool   | `false` | If `true`, returns only the video URL instead of a `<video>` tag.                        |
| `video_id`          | string | `null`  | Video ID from ['Big_Buck_Bunny', 'Jellyfish', 'Sintel']. Defaults to a random selection. |
| `video_size`        | string | `1080`  | Video resolution size from ['1080', '720', '360'].                                       |
| `video_extension`   | string | `mp4`   | Video file extension from ['mp4', 'webm', 'mkv'].                                        |
| `video_autoplay`    | bool   | `false` | If `true`, autoplay the video. Only works if `url_only` is set to `false`.               |
| `video_controls`    | bool   | `true`  | If `true`, show video controls. Only works if `url_only` is set to `false`.              |
| `video_loop`        | bool   | `false` | If `true`, loop the video. Only works if `url_only` is set to `false`.                   |
| `video_muted`       | bool   | `false` | If `true`, mute the video. Only works if `url_only` is set to `false`.                   |
| `video_playsinline` | bool   | `true`  | If `true`, play the video inline. Only works if `url_only` is set to `false`.            |

#### **Examples:**

**Generate a default placeholder video:**
```twig
{{ tp_video() }}
```
_Output:_
```html
<video controls playsinline width="1920" height="1080">
  <source src="https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/1080/Big_Buck_Bunny_10s_1MB.mp4" type="video/mp4" />
  Your browser does not support HTML5 video. <a href="https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/1080/Big_Buck_Bunny_10s_1MB.mp4">Download the video</a> instead.
</video>
```

**Generate only the video URL:**
```twig
{{ tp_video(true) }}
```
_Output:_
```text
https://test-videos.co.uk/vids/jellyfish/mp4/h264/1080/Jellyfish_10s_1MB.mp4
```

**Generate a random video with autoplay, no controls, loop, muted, and playsinline to use as a background video:**
```twig
{{ tp_video(false, null, null, null, true, false, true, true, true) }}
```
_Output:_
```html
<video autoplay loop muted playsinline width="1920" height="1080">
  <source src="https://test-videos.co.uk/vids/sintel/mp4/h264/1080/Sintel_10s_1MB.mp4" type="video/mp4" />
  Your browser does not support HTML5 video. <a href="https://test-videos.co.uk/vids/sintel/mp4/h264/1080/Sintel_10s_1MB.mp4">Download the video</a> instead.
</video>
```

**Generate a URL for the jellyfish video in 360p with mkv extension:**
```twig
{{ tp_video(true, 'Jellyfish', '360', 'mkv') }}
```
_Output:_
```text
https://test-videos.co.uk/vids/jellyfish/mkv/360/Jellyfish_10s_1MB.mkv
```

### Generating Placeholder Menu Data
The `tp_menu_data` function generates an array of menu data in the typical Drupal structure.

```twig
{% include 'my_theme:menu' with {
  attributes: create_attribute(),
  items: tp_menu_data(),
} %}
```

#### NOTICE: The tp_menu_data() twig function will break your website if called directly: {{ tp_menu_data() }}. This function is not supposed to be rendered as a whole but instead iterated through by your code or component.

#### **Optional Arguments:**
| Parameter   | Type  | Default                | Description                                                                                                                                                                                                                                                                                                  |
|-------------|-------|------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `arguments` | array | `[[3,6],[0,10],[0,8]]` | An array of arrays made up of min and max children values, moving down a tree. The first array is min and max number of menu items. The second array is min and max number of child menu items per menu item. The third array is min and max number of grandchild menu items per child menu item. And so on. |

#### **Examples:**

**Generate array of 3-6 menu items, with 0-10 children each, and 0-8 grandchildren per child:**
```twig
{{ dump(tp_menu_data()) }}
```
_Output:_
```twig
[
  {
    "attributes": [],
    "title": "Lorem ipsum",
    "url": "#",
    "below": [
      {
        "attributes": [],
        "title": "Dolor sit",
        "url": "#",
        "below": [
          {
            {
              "attributes": [],
              "title": "Amet consectetur",
              "url": "#",
            }
          },
          ... and up to 7 more of these!
        ],
      }
      ... and up to 9 more of these!
    ],
  },
  ... and 2-5 more of these!
]
```

**Generate array of exactly 5 menu items, with 2-3 children each, and 5-10 grandchildren per child:**
```twig
{{ dump(tp_menu_data([[5,5],[2-3],[5-10]])) }}
```
_Output:_
```twig
[
  {
    "attributes": [],
    "title": "Lorem ipsum",
    "url": "#",
    "below": [
      {
        "attributes": [],
        "title": "Dolor sit",
        "url": "#",
        "below": [
          {
            {
              "attributes": [],
              "title": "Amet consectetur",
              "url": "#",
            }
          },
          ... and 4-9 more of these!
        ],
      }
      ... and 1-2 more of these!
    ],
  },
  ... and exactly 4 more of these!
]
```

### Generating Placeholder Select Data
The `tp_select_data` function generates a list of `<option>` placeholder data with optional `<optgroup>` wrappers and the ability to equally distribute the options among the optgroups or randomly place them instead.

```twig
{% include 'my_theme:select' with {
  attributes: create_attribute(),
  options: tp_select_data(),
} %}
```

#### NOTICE: The tp_select_data() twig function will break your website if called directly: {{ tp_table_data() }}. This function is not supposed to be rendered as a whole but instead iterated through by your code or component.

#### **Optional Arguments:**
| Parameter       | Type | Default | Description                                                                                                                      |
|-----------------|------|---------|----------------------------------------------------------------------------------------------------------------------------------|
| `num_items`     | int  | `10`    | The total number of options to return.                                                                                           |
| `num_optgroups` | int  | `0`     | The total number of optgroups to return.                                                                                         |
| `randomise`     | bool | `false` | If `true` options will be distributed randomly across optgroups. If `false` options will be distributed equally among optgroups. |

#### **Examples:**

**Generate 10 select data items without optgroups:**
```twig
{{ dump(tp_select_data()) }}
```
_Output:_
```twig
[
  {"type": "option", "value": "item_1", "label": "Lorem ipsum", "selected": false},
  {"type": "option", "value": "item_2", "label": "Dolor sit", "selected": false},
  {"type": "option", "value": "item_3", "label": "Amet consectetur", "selected": false},
  {"type": "option", "value": "item_1", "label": "Adipiscing elit", "selected": false},
  {"type": "option", "value": "item_2", "label": "Nullam suscipit", "selected": false},
  {"type": "option", "value": "item_3", "label": "Vitae felis", "selected": false},
  {"type": "option", "value": "item_1", "label": "Et convallis", "selected": false},
  {"type": "option", "value": "item_2", "label": "Nibh vitae", "selected": false},
  {"type": "option", "value": "item_3", "label": "Eros tempor", "selected": false},
  {"type": "option", "value": "item_3", "label": "Tempus sit", "selected": false}
]
```

**Generate 4 select data items across 2 optgroups:**
```twig
{{ dump(tp_select_data(4, 2)) }}
```
_Output:_
```twig
[
  {"type": "optgroup", "label": "Nullam suscipit", "options": [
    {"type": "option", "value": "item_1", "label": "Lorem ipsum", "selected": false},
    {"type": "option", "value": "item_2", "label": "Dolor sit", "selected": false}
  ]},
  {"type": "optgroup", "label": "Vitae felis", "options": [
    {"type": "option", "value": "item_3", "label": "Amet consectetur", "selected": false},
    {"type": "option", "value": "item_4", "label": "Adipiscing elit", "selected": false}
  ]}
]
```

**Generate 6 select data items across 2 optgroups, distributed randomly:**
```twig
{{ dump(tp_select_data(6, 2, true)) }}
```
_Output:_
```twig
[
  {"type": "optgroup", "label": "Nullam suscipit", "options": [
    {"type": "option", "value": "item_5", "label": "Nibh vitae", "selected": false},
    {"type": "option", "value": "item_2", "label": "Et convallis", "selected": false},
    {"type": "option", "value": "item_6", "label": "Lorem ipsum", "selected": false},
    {"type": "option", "value": "item_1", "label": "Dolor sit", "selected": false}
  ]},
  {"type": "optgroup", "label": "Vitae felis", "options": [
    {"type": "option", "value": "item_4", "label": "Amet consectetur", "selected": false},
    {"type": "option", "value": "item_3", "label": "Adipiscing elit", "selected": false}
  ]}
]
```

### Generating Placeholder Table Data
The `tp_table_data` function generates an array of placeholder table data with an optional number of rows and columns. It also includes an optional header argument that can force the first row to be `<th>` elements or allow you to specify whether to use `<td>` or `<th>` for all table cells.

```twig
{% include 'my_theme:table' with {
  caption: null,
  header: null,
  rows: tp_table_data(),
} %}
```

#### NOTICE: The tp_table_data() twig function will break your website if called directly: {{ tp_table_data() }}. This function is not supposed to be rendered as a whole but instead iterated through by your code or component.

#### **Optional Arguments:**
| Parameter          | Type        | Default | Description                                                                                                                                                                                                                                                                                                        |
|--------------------|-------------|---------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `$num_rows`        | int         | `10`    | The total number of rows to return.                                                                                                                                                                                                                                                                                |
| `$num_cols`        | int         | `4`     | The total number of columns to return.                                                                                                                                                                                                                                                                             |
| `$header_row_opts` | bool/string | `false` | Options for the header row from [true, false, 'th', 'td'].<br>If `false` no header row will be generated.<br>If `true` the first row uses `<th>` tag and all others use `<td>`.<br>If `'th'` all cells use `<th>` and structure is flattened.<br>If `'td'` all cells to use `<td>` which is equivalent to `false`. |

#### **Examples:**

**Generate table data items for 10 rows and 4 columns:**
```twig
{{ dump(tp_select_data()) }}
```
_Output:_
```twig
[
  {
    attributes: [],
    cells: [
      {tag: 'td', attributes: [], content: 'Lorem ipsum'},
      {tag: 'td', attributes: [], content: 'Dolor sit'},
      {tag: 'td', attributes: [], content: 'Amet consectetur'},
      {tag: 'td', attributes: [], content: 'Adipiscing elit'}
    ]
  },
  {
    attributes: [],
    cells: [
      {tag: 'td', attributes: [], content: 'Nullam suscipit'},
      {tag: 'td', attributes: [], content: 'Vitae felis'},
      {tag: 'td', attributes: [], content: 'Et convallis'},
      {tag: 'td', attributes: [], content: 'Nibh vitae'}
    ]
  }
  ... + 8 more rows ...
]
```

**Generate table data items for 5 rows and 4 columns, with the first row as `<th>` cells:**
```twig
{{ dump(tp_table_data(5, 4, "th")) }}
```
_Output:_
```twig
[
  {
    attributes: [],
    cells: [
      {tag: 'th', attributes: [], content: 'Lorem ipsum'},
      {tag: 'th', attributes: [], content: 'Dolor sit'},
      {tag: 'th', attributes: [], content: 'Amet consectetur'},
      {tag: 'th', attributes: [], content: 'Adipiscing elit'}
    ]
  },
  {
    attributes: [],
    cells: [
      {tag: 'td', attributes: [], content: 'Nullam suscipit'},
      {tag: 'td', attributes: [], content: 'Vitae felis'},
      {tag: 'td', attributes: [], content: 'Et convallis'},
      {tag: 'td', attributes: [], content: 'Nibh vitae'}
    ]
  }
  ... + 3 more rows ...
]
```

**Generate table data items for 2 rows and 3 columns:**
```twig
{{ dump(tp_select_data(2, 3)) }}
```
_Output:_
```twig
[
  {
    attributes: [],
    cells: [
      {tag: 'td', attributes: [], content: 'Lorem ipsum'},
      {tag: 'td', attributes: [], content: 'Dolor sit'},
      {tag: 'td', attributes: [], content: 'Amet consectetur'}
    ]
  },
  {
    attributes: [],
    cells: [
      {tag: 'td', attributes: [], content: 'Adipiscing elit'},
      {tag: 'td', attributes: [], content: 'Nullam suscipit'},
      {tag: 'td', attributes: [], content: 'Vitae felis'}
    ]
  }
]
```

## Requirements

- Drupal **10.3+**
- A custom or existing theme to use Twig placeholder functionality.

## Installation

Install via Composer:
```bash
composer require drupal/twig_placeholders
```

Enable the module using Drush or the Drupal UI:
```bash
drush en twig_placeholders
```

## Support & Contributions

If you encounter any issues or have feature requests, please submit them on the [Twig Placeholders issue queue](https://www.drupal.org/project/twig_placeholders/issues).

Contributions are welcome! Feel free to submit a pull request or suggest improvements.

---

Enjoy using **Twig Placeholders** to demo all of your best Drupal components! 😎
