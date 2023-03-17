<?php

namespace Drupal\Tests\bean_migrate\Kernel;

/**
 * Tests bean migrations from multilingual source.
 *
 * @group bean_migrate
 */
class BeanMigrateMultilingualTest extends BeanMigrateTestBase {

  /**
   * {@inheritdoc}
   */
  protected $isMultilingualTest = TRUE;

  /**
   * Tests the migrations.
   */
  public function testMigrations() {
    $this->executeBeanMigrations();

    $this->performBeanMigrationAssertions();
  }

}
