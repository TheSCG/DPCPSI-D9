CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Usage
 * Maintainers


INTRODUCTION
------------

The [Drupal 7 Bean module][1] allows users to create blocks of content (Bean
entities), which can be placed in regions throughout the website. Bean entities
can have fields, view modes; they have revisions; and can be placed into regions
like blocks provided by other modules. It is the ancestor of the Custom Block
module which was added to Drupal 8 core.

This module, [Bean Migrate][2], migrates the Drupal 7 Bean entities into custom
block (`block_custom`) content entities including all of their revisions.


REQUIREMENTS
------------

This module depends on the following modules:

* Custom Block (included in Drupal core)
* Migrate Drupal (included in Drupal core)


INSTALLATION
------------

You can install Bean Migrate as you would normally install a contributed
Drupal 8 or 9 module.


CONFIGURATION
-------------

This module does not have any configuration option.


USAGE
-----

The module provides no extra features on its own, it just provides migration
plugins for the migrations of Bean entities (including not only the content, but
also the configuration migration required for storing the entity fields on the
destination site).

This means that users might use these migration plugins like they use the ones
in Drupal core:
* Users might use the [Migrate Drupal UI][3] to migrate a Drupal 7 source site
  to Drupal 9
* Users chose use the contributed module [Migrate Upgrade][4] to export the
  migration plugins to [Migrate Plus][5] configuration entities; customize them
  and execute them with Drush.
* And last but not least, all of the migration source and destination plugins
  can be used for writing custom migration plugin definitions.


MAINTAINERS
-----------

* Zoltán Horváth (huzooka) - https://www.drupal.org/u/huzooka

This project has been sponsored by [Acquia][6].

[1]: https://drupal.org/node/1149602
[2]: https://drupal.org/node/3194043
[3]: https://drupal.org/node/2829465
[4]: https://drupal.org/node/2271813
[5]: https://drupal.org/node/2202391
[6]: https://acquia.com
