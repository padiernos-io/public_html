# Devutils
Development utilities

## Drush command that export uuid for entities.
```shell
drush devutils:uuid
```

- Allows to obtain the uuid of the entities, menu_link, block, node, media, file, term, paragraph.
- Presents a filter to improve the search in the entities.
- Introduces the --label option to display the entity's label.

### Example to get all the uuid of the node entity:

```
drush devutils:uuid node
```
#### Results:

```yml
- 6666cc66-6666-6666-6666-66cb6f66e661
- 6666cc66-6666-6666-6666-66cb6f66e662
- 6666cc66-6666-6666-6666-66cb6f66e663
- 6666cc66-6666-6666-6666-66cb6f66e664
```

### Example to get all the uuid of the node entity with label:
```shell
drush devutils:uuid node --label
```

#### Results:
```yml
# Page 1
- 6666cc66-6666-6666-6666-66cb6f66e661
# Page 2
- 6666cc66-6666-6666-6666-66cb6f66e663
```

###Example to obtain all the uuid of the type page node entity::
```shell
drush devutils:uuid node page
```
####Results:
```
- 6666cc66-6666-6666-6666-66cb6f66e663
- 6666cc66-6666-6666-6666-66cb6f66e664
```

##Drush command that clear not used file.
```shell
drush devutils:clear-files
```
- Allows you to delete all files that are not being used.

### Example to remove all files:
```shell
drush devutils:clear-files
```

## Servicio devutils.update_import

- This service updates the specific configurations of a module. Mainly, it is used in Updates to update one or more specific configurations of a module.
- Create and update the file.storage.
- Import the configuration translations if they are found in the files.
- Does not import different collections other than translations.


### Example:
#### Configure the Hook Update:
##### How to configure to import a file
```php
Drupal::service('devutils.update_import')->import('my_module','file_config');
```
##### How to configure to import all module files
```php
Drupal::service('devutils.update_import')
  ->import('my_module');
```
##### How to configure to import specific files from a module
```php
$configs = ['file_config1',
            'file_config2',
            'file_config3'];
Drupal::service('devutils.update_import')->import('my_module',$configs);
```

#### my_module:
- Module to be updated.

#### file_config:
- Configuration files to be updated that are inside the module, in the config/install or config/optional folder.
