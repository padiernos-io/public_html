# Entity Usage Explorer

**Entity Usage Explorer** is a module designed to accurately track and display where entities are being used throughout a site, based on current revision of entities.

## Features
- **Views Field Plugin:**
  - Provides a field plugin `Base Entity Usage` to list the total places where an entity is used.
  - Two rendering options:
    1. **Plain Text:** Displays the count as plain text.
    2. **Link:** Displays the count as a clickable link, taking you to the Entity Usage Overview page.
- **Operations Link:**
  - Adds a "Usage" operations link for all content entities.
- **Entity Usage Listing:**
  - Lists entity usage across various types, including Menu Links, Nodes, Media, Block Content, Users, Paragraphs, etc.
- **Usage Overview Page:** (`/admin/usage/{entity_type}/{entity_id}`)
  - Provides links to entities where the target entity is being used.
  - If the referencing entity does not have a canonical path (e.g., Paragraphs), it provides a link to the usage overview of its parent entity (e.g., a link to a Node where the Paragraph is referenced).

## Requirements
This module requires no modules outside of Drupal core.

## Installation

1. Install via composer (`composer require drupal/entity_usage_explorer`) or manually place in the `/modules/custom directory`.

2. Enable the module via the UI or Drush:
   ```bash
   drush en entity_usage_explorer
   drush cr
   ```

## Configuration

### Custom Views Field
- Add the `Base Entity Usage` field to your View.
- In the field settings, choose the **Render Type**:
  - `Plain Text`: Displays the count as plain text.
  - `Link`: Displays the count as a clickable link to the overview page.
