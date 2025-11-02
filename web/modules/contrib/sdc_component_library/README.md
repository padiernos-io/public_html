SDC - Components library Module
Overview

The Finalist Components module provides a visual preview of components defined in a Drupal theme. Components are represented using .story.twig files located in the components directory of the active theme, with dummy data included. The module dynamically renders these components at the /components page, making it easier for developers to see how components look with example data.
Features

    Automatically locates and renders .story.twig files from the active theme's components folder.
    Allows developers to visually preview components at /components.
    Supports the inclusion of dummy data via a .component.yml file for each component, enabling realistic previews.
    Dynamically adapts to the active theme without hardcoding the theme name.

How It Works

    In your theme's components directory, create your component templates (e.g., button, card, etc.). Each component has two key files:
        A .story.twig file: This is used for visualizing the component, and it includes the main component template.
        An optional .component.yml file: Defines the component’s metadata and dummy data (like props) used in the preview.

    In the .story.twig file, you will include the actual component template along with dummy data, typically provided in the .component.yml file.

    When you visit /components, the module automatically scans the active theme for all .story.twig files, renders them, and displays them on a single page. This gives you a visual overview of how your components look and behave with the example data.

Requirements

    A Drupal 9+ installation.
    A custom or existing theme with a components folder structure as outlined below.

Installation

    Clone or download this module into your /modules/custom folder.

    Enable the module using the following Drush command or the Drupal UI:

    bash

    drush en sdc_component_library

    Ensure your theme contains a components directory where you store your component templates.

File Structure

The components in your theme should follow this structure:

css

/themes/custom/your_theme/components/
├── atoms/
│   ├── button.story.twig
│   ├── button.twig
│   ├── button.component.yml
├── molecules/
│   ├── card.story.twig
│   ├── card.twig
│   ├── card.component.yml
└── ...

    button.story.twig: The Twig template that includes the main component with example data.
    button.component.yml: The YAML file that defines the properties (props) and example data for the component.

Example .story.twig

For each component, the .story.twig file includes the actual component with dummy data:

twig

{# button.story.twig #}
{% include '@active_theme/components/atoms/button.twig' with {
label: 'Click Me',
icon: 'arrow-right'
} only %}

Example .component.yml

The optional .component.yml file stores metadata about the component and example data:

yaml

name: Button Component
description: A simple button component
group: Atoms
props:
properties:
label:
type: string
examples:
- "Click Me"
icon:
type: string
examples:
- "arrow-right"

Usage

Once you have set up your components, visit /components in your browser. The module will automatically scan for .story.twig files in your theme's components directory and render each component with the provided dummy data.

The page will display:

    The name and description of the component (from the YAML file).
    The rendered component itself (from the .story.twig file).

Customization

    Component Data: Customize the data displayed in your components by editing the corresponding .component.yml file.
    Component Organization: Organize components into different folders (e.g., atoms, molecules, organisms) under your theme's components directory.

Extending

You can extend this module by:

    Adding more complex example data to your .component.yml files.
    Introducing custom styling or JS libraries by attaching them in the libraries.yml file.

Support

If you encounter any issues or have questions about the module, please feel free to submit a support request or contribute on the project's repository.
