<?php

namespace Drupal\Tests\bean_migrate\Kernel;

/**
 * Tests bean migrations.
 *
 * @group bean_migrate
 */
class BeanMigrateTest extends BeanMigrateTestBase {

  /**
   * Tests the migrations.
   */
  public function testMigrations() {
    $this->executeBeanMigrations();

    $this->performBeanMigrationAssertions();
  }

  /**
   * Tests bean migrations without bean UUIDs.
   *
   * @depends testMigrations
   */
  public function testMigrationsWithoutUuid() {
    $this->sourceDatabase->update('system')
      ->condition('name', 'bean_uuid')
      ->fields(['status' => 0])
      ->execute();
    $this->sourceDatabase->schema()->dropField('bean', 'uuid');
    $this->sourceDatabase->schema()->dropField('bean_revision', 'vuuid');

    $this->testMigrations();
  }

}
