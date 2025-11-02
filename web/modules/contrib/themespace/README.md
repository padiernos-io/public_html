CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Provider Typed Plugins
 * Maintainers


INTRODUCTION
------------

Adds enabled theme namespaces to the "containers.themespaces" parameter and
a "themespace.namespaces" service to the dependency injection container. This
allows themes to define classes and have discoverable class attributes plugins.

Theme namespaces are added to PSR4 and the class loader by the
Drupal\Core\Extension\ThemeHandler::addTheme() method. This module adds the
ability to traverse namespaces of enabled themes in order to allow plugin
attribute discovery in themes.

The module also provides ProviderTypedPlugindDefinitions and attributes to
ensure that theme plugins and module provided plugins are identified and that
plugin managers are able to handle them separately. Theme plugins in general
should only be available when the providing theme is the active theme or from
a base theme of the active theme.

Module does not add any functionality on its own and only provides utility for
module and theme developers to use OOP techniques in themes or to create new
plugin APIs that support plugin discovery with themes.

See the __Provider Typed Plugins__ section for more information about using
plugin definition and discovery classes for plugins that are aware of their
provider's extension type (module or theme).

**Warning:** Version 3 corrects issues the incorrect naming convention for theme
namespaces, and by doing so can cause breaking changes if you use the previoulsy
suggested theme namespace convention (`\Drupal\Theme\<theme name>`). Instead
use `\Drupal\<theme name>` which will now match the Drupal namespacing
convention for modules and themes. Also instead of using `container.namespaces`
for your plugin manager namespace traversable, replace this with the
'themespace.namespaces' service in order to use provider typed plugin discovery.


 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/themespace

 * To submit bug reports and feature suggestions, or track changes:
   https://www.drupal.org/project/issues/themespace


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


INSTALLATION
------------

Install as you would normally install a contributed Drupal module. Visit
https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

This module requires no extra configurations.


PROVIDER TYPED PLUGINS
----------------------

Provider typed plugins are plugins which know the provider's extension type
("theme" or "module"). In order to support this additional information
themespace adds annotation, plugin definition, and plugin discovery classes.

It is important when creating plugin APIs that support theme defined plugins
that they know if they are from a theme or module. Plugin developers are
responsible for managing how their plugins behave based on the **active theme**.


### Attribute & Plugin Definition

The Themespace module extends the Drupal core plugin attribute and plugin
definitions simply to add functionality for storing and retrieving the plugin's
provider type.

These are the expected attribute and definitions classes to use when working
with a `\Drupal\themespace\Plugin\Discovery\ProviderTypedDiscoveryInstance`
suite of plugin discovery classes.

`@see \Drupal\themespace\Attribute\ProviderTypedPluginInterface`
`@see \Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinitionInterface`


### Plugin Discovery

Themespace adds plugin discovery handlers and decorators which are meant to work
with the provider typed annotations and provider extension type information.
They all implement the `ProviderTypedDiscoveryInterface`, which adds the
`getModuleDefinitions()` and `getThemeDefinitions()` methods. All the included
plugin discovery handlers and decorators will populate the `provider_type`
information for the plugin definition and allow *module* and *theme* definitions
to be found separately.

When implementing plugins that make use of Themespace's ability to define theme
plugins and namespaces, it is recommended to make use of these discovery
handlers with your plugin manager.

`@see \Drupal\themespace\Plugin\Discovery\ProviderTypedAttributeClassDiscovery`
`@see \Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscovery`
`@see \Drupal\themespace\Plugin\Discovery\ProviderTypedDeriverDiscoveryDecorator`
`@see \Drupal\themespace\Plugin\Discovery\ProviderTypedYamlDiscoveryDecorator`


### Plugin Managers

Plugin implmenters are responsible for making sure their plugins handle theme
provided plugins in a expected and appropriate way. Generally module plugins
should always be active, but theme based plugins should be respectful of which
active theme is currently active.

How a plugin manager should implement this depends on the intended usage of the
plugins and when they are applied.


#### **Plugins Built per Theme**

Plugins which are applied to an API that is **cached per theme** can just assign
plugin IDs or definitions based on theme information being built. An example
would be plugins that are applied during `hook_theme_registry_alter()`. The
registry alter hook is called per theme, and cached after the registry build is
completed for the target theme.

Plugin managers for this situation can use the
`\Drupal\themespace\Plugin\ProviderTypedPluginManagerTrait` and apply the
plugin definitions from `getModuleDefinitions()` and `getDefinitionsByTheme()`
with the target theme as the argument. This makes all module definitions
available, but only the plugins of the target theme available during the theme
registry build hook.


#### Plugins with Theme Overrides

With provider typed discovery handlers it is possible to discover module and
theme plugin definitions separate, which allows create plugin managers that
load module plugins (and cache definitions separately) and load theme
definitions by active theme to override module definitions per theme.


ROADMAP
-------

 * More examples and use cases for plugin managers
 * Re-evaluate the provider typed plugin naming for a 3.x version


MAINTAINERS
-----------

Current maintainers:
 * Liem Khuu (lemming) - https://www.drupal.org/u/lemming
