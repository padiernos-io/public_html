# Media Name

If the Media name is displayed on the create/edit form
- make it not mandatory
- if the name has been set previously or updated, preserve it on file change

Preserve the default core behaviour:
- populate the name with the file name if it is left empty
- if it has the same name as the file name, update it on file update
(it can still be overridden via configuration:
/admin/config/media/media-name/settings).

### Example use case

For most Media types/bundles it does not make sense to
expose the name field. In some cases e.g. Documents,
it could make sense to have a persistent file name even
if the file is replaced. Example: say we have a Media Document
with the name "User manual" and file "user_manual_v1.pdf",
when the file is replaced by "user_manual_v2.pdf"
we want to preserve the name.

### Configuration

There is no specific configuration apart from exposing the Media name
in the Media edit form
(e.g. /admin/structure/media/manage/document/form-display).

### Related Media closed issues / documentation

- [Hide the media name basefield from the entity form by default](https://www.drupal.org/project/drupal/issues/2882473)
- [Make media name available on manage display](https://www.drupal.org/project/drupal/issues/2912298)
- [Using the "automatic name" functionality for your media entities](https://drupal-media.gitbooks.io/drupal8-guide/content/modules/media_entity/auto_name.html)

### Related module

Not the same use case, but similar for file names.

[Media Entity File Replace](https://www.drupal.org/project/media_entity_file_replace)

### Test cases to implement

Given the Media name text input is visible on the Media create and edit form.

- The Media name should not be set as required
- If I create a Media with no name, the name is set from the file name
- If I create a Media with a custom name, it is preserved
- If I update a Media with a custom name, it is preserved
- If I update a Media with a custom name and replace the file, it is preserved
- If I update a Media with a custom name, change the name and replace the file,
 the new name is preserved
