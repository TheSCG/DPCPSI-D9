<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate;

use Drupal\migrate\Plugin\Migration;
use Drupal\Tests\bean_migrate\Kernel\BeanMigrateTestBase;
use PHPUnit\Util\Test;

/**
 * Base class for testing the derivers of Bean Migrate.
 */
abstract class BeanDeriverTestBase extends BeanMigrateTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getMigration($plugin_id) {
    $covered_deriver_class = ltrim($this->getCoversClass(), '\\');
    $migration = parent::getMigration($plugin_id);
    $this->assertInstanceOf(Migration::class, $migration);
    $this->assertEquals($covered_deriver_class, $migration->getPluginDefinition()['deriver']);
    return $migration;
  }

  /**
   * Returns a preprocessed migration plugin definition.
   *
   * This method strips out the environment-specific absolute path form the
   * given migration's "_discovered_file_path" key.
   *
   * @param \Drupal\migrate\Plugin\Migration $migration
   *   The migration plugin instance.
   *
   * @return array
   *   The cleaned plugin definition of the given migration plugin instance.
   */
  protected static function getImportantMigrationDefinitionProperties(Migration $migration): array {
    $definition = $migration->getPluginDefinition();
    $module_path = preg_quote(DRUPAL_ROOT . '/' . drupal_get_path('module', $definition['provider']), '/');
    $definition['_discovered_file_path'] = preg_replace("/{$module_path}/", '', $definition['_discovered_file_path']);
    return $definition;
  }

  /**
   * Determines the deriver to be tested by reading the "@covers" annotation.
   *
   * @return string
   *   The covered class.
   */
  protected function getCoversClass(): string {
    $annotations = Test::parseTestMethodAnnotations(
      static::class,
      $this->getName()
    );

    if (isset($annotations['class']['covers'])) {
      return $annotations['class']['covers'][0];
    }
    else {
      $this->fail('No class was specified');
    }
  }

}
