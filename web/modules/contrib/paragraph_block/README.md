# Paragraph Block

## CONTENTS OF THIS FILE

- Introduction
- Requirements
- Installation
- Configuration
- Maintainers

## INTRODUCTION

The Paragraph Block module enables the use of paragraph types as block types
without additional block type configuration. It provides per-paragraph type
configurability, offering flexibility and seamless integration with Layout
Builder for an enhanced user experience. By leveraging the default block content
entity for storage, the module ensures a minimally invasive implementation with
a robust out-of-the-box experience.

## REQUIREMENTS

- [Paragraphs](https://www.drupal.org/project/paragraphs)
- [Form Decorator](https://www.drupal.org/project/form_decorator)
- [Block Form Alter](https://www.drupal.org/project/block_form_alter)
- Block content (included in Drupal core)
- (Optional) Layout Builder (included in Drupal core)

## INSTALLATION

Install the module as you would normally install a contributed Drupal module.
Refer to [installing modules](https://www.drupal.org/node/1897420) for detailed
instructions.

## CONFIGURATION

1. Navigate to `/admin/structure/paragraphs_type`.
2. Create or edit a paragraph type.
3. Enable the Paragraph Block setting under "Paragraph block settings".
4. A new block type corresponding to the paragraph will now be available at
`/block/add` or within Layout Builder, preconfigured with the selected
paragraph.

## MAINTAINERS

Current maintainers:

- Pascal Crott - [hydra](https://www.drupal.org/u/hydra)
