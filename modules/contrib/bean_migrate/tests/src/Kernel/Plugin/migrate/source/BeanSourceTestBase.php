<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

use Drupal\Tests\migmag\Kernel\MigMagNativeMigrateSqlTestBase;

/**
 * Base class for testing bean source plugins with native databases.
 *
 * @see \Drupal\Tests\migrate\Kernel\MigrateTestBase
 */
abstract class BeanSourceTestBase extends MigMagNativeMigrateSqlTestBase {

  /**
   * {@inheritdoc}
   *
   * @todo This should be changed to "protected" after Drupal core 8.x security
   *   support ends.
   * @see https://www.drupal.org/node/2909426
   */
  public static $modules = [
    'bean_migrate',
    'migrate_drupal',
  ];

}
