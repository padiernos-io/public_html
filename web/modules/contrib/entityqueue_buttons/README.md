# Entityqueue Buttons

## Introduction

Entityqueue Buttons is a Drupal module that adds buttons to nodes, making it fast (one click) to:

1. Add the node to an Entityqueue.
2. Remove the node from an Entityqueue.

Additionally, it provides a settings page to manage Entityqueue assignments per content type.

## Requirements

- Drupal 10 or later
- [Entityqueue](https://www.drupal.org/project/entityqueue) module

## Installation

1. Install the Entityqueue Buttons module as you would any other Drupal module. Refer to the [Drupal documentation on installing modules](https://www.drupal.org/docs/extending-drupal/installing-modules) for guidance.
2. Ensure that the Entityqueue module is installed and enabled.

## Configuration

1. Navigate to the settings page at `/admin/config/content/entityqueue-buttons` to decide which buttons to add to your content types.
2. Go to the permissions page at `/admin/people/permissions` and assign permissions to roles that should administer and use Entityqueue Buttons.
3. To customize the appearance of the buttons, adjust your CSS or modify the provided Twig template at `entityqueue_buttons/templates/entityqueue-buttons.html.twig`.

## Usage

Once configured, the buttons will appear on nodes of the selected content types, allowing users with the appropriate permissions to quickly add or remove the node from an Entityqueue directly from the node page.

## Troubleshooting

If you encounter issues:

- Verify that the Entityqueue module is installed and enabled.
- Check permissions to ensure users have the correct access.
- Clear the cache after making configuration changes.

## FAQ

**Q:** Can I customize how the buttons look?

**A:** Yes, you can style the buttons using CSS or modify the Twig template provided.

## Maintainers

- [Steven Snedker](https://www.drupal.org/u/steven-snedker)
