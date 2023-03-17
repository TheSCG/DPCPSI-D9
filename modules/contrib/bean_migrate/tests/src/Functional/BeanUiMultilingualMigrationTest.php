<?php

namespace Drupal\Tests\bean_migrate\Functional;

/**
 * Tests Bean Migrate and core UI compatibility with multilingual source.
 *
 * @group bean_migrate
 */
class BeanUiMultilingualMigrationTest extends BeanUiMigrationTestBase {

  /**
   * {@inheritdoc}
   */
  protected $isMultilingualTest = TRUE;

  /**
   * Tests Bean Migrate and core migrations compatibility.
   */
  public function testMigrationsWithBean() {
    $this->executeMigrationsWithUi();

    // We have to reset all the static caches after migration to ensure entities
    // are loadable.
    $this->resetAll();

    $this->performBeanMigrationAssertions();
  }

}
