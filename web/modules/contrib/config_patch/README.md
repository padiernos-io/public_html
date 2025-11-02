With strict configuration management, config always moves from development to production as part of an automated deployment process. But this ideal can fall apart quickly in the real world. If a sitebuilder makes a quick configuration change on production, how do we make sure this change is propagated back to the source repository and not overwritten by the next deployment?

This module provides a framework for creating and applying patches for config (the differences between active configuration and sync configuration). This allows sitebuilders to make changes to configuration in the UI and push a button to create a patch which may then be committed back to source. The framework is extensible, so additional output plugins may provide integrations with e.g., cloud source control providers. For instance, submodules listed below can be used to create PRs on Github or MRs on Gitlab.

Since patches reference config yml file paths, the base path of these files is configurable. If your synchronized config is stored in `drupal/config` in your source repo, you might configure `drupal/config` as your base config path in config_patch. If on the other hand, your various repositories and environments all follow the same Drupal path structure, insert something like the following into settings.php for zero module configuration:

<code>
$config['config_patch.settings']['config_base_path'] = 'web/' . $settings['config_sync_directory'];
</code>

(below where config_sync directory has been defined).

When installed, config_patch provides an additional tab `Patch` on the admin config synchronization pages listing config that's out of sync and providing a button to output the patch in various forms. The action of the button is controlled by the currently selected output plugin.

A helpful widget will also appear in the toolbar revealing the number of config items differing between sync and active storage. Clicking the widget will take you to the config sync `Patch` tab.

<h2>Available output plugins</h2>

- Text: (included with `config_patch`) provides text output of a unified diff patch
- [Gitlab](https://drupal.org/project/config_patch_gitlab): provides submission to Gitlab repositories using MR-by-email functionality.
- [Gitlab API](https://www.drupal.org/project/config_patch_gitlab_api): create an MR in Gitlab directly using their API. The module description page has great illustrations of the patching workflow.
- [Gitea](https://www.drupal.org/project/config_patch_gitea): create an PR on Gitea
- [Azure API](https://www.drupal.org/project/config_patch_azure_api): push config to any Azure instance by using the Azure DevOps API.
- [Github API](https://www.drupal.org/project/config_patch_github_api): create an PR in Gitlab directly using their API

<h2>Similar modules</h2>

- [Config Partial Export](https://www.drupal.org/project/config_partial_export): export a tarball of config ymls that differ between sync and active
- [Config PR](https://www.drupal.org/project/config_pr): very similar to this module; make a PR in Github, Gitlab, or Bitbucket based on a config patchset

<h2>Works with</h2>

- [Config Ignore](https://www.drupal.org/project/config_ignore): `config_patch` will respect `config_ignore` settings, if enabled.

<h2>Drush integration</h2>

<code>
drush config:patch PLUGIN_ID  # create a patch via a plugin
drush config:patch:list       # list files that have changed
</code>

If you have a drush alias (or similar way to run drush on a remote site), you can easily apply config changes from that site to your local repo:

<code>
drush cpatch text|patch -p1
</code>