<?php

namespace Drupal\Tests\bean_migrate\Kernel;

/**
 * Tests bean migrations with a "basic" type.
 *
 * @group bean_migrate
 */
class BeanMigrateBasicTypeTest extends BeanMigrateTestBase {

  /**
   * Tests the migrations with a "basic" bean type instead of "simple".
   */
  public function testMigrations() {
    foreach (['bean', 'bean_revision'] as $table) {
      $this->sourceDatabase->update($table)
        ->fields(['type' => 'basic'])
        ->condition('type', 'simple')
        ->execute();
    }
    $this->sourceDatabase->update('bean_type')
      ->fields(['name' => 'basic'])
      ->condition('name', 'simple')
      ->execute();
    $this->sourceDatabase->update('variable')
      ->fields(['name' => 'field_bundle_settings_bean__basic'])
      ->condition('name', 'field_bundle_settings_bean__simple')
      ->execute();
    $this->sourceDatabase->update('variable')
      ->fields(['name' => 'entity_translation_settings_bean__basic'])
      ->condition('name', 'entity_translation_settings_bean__simple')
      ->execute();

    $field_tables = [
      'field_config_instance',
      'field_data_node_body',
      'field_revision_field_body',
    ];
    foreach ($field_tables as $table) {
      $this->sourceDatabase->update($table)
        ->fields(['bundle' => 'basic'])
        ->condition('bundle', 'simple')
        ->execute();
    }

    $this->executeBeanMigrations();

    $this->performBeanMigrationAssertions('basic_bean_1');
  }

}
