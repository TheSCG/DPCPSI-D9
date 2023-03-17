<?php

namespace Drupal\bean_migrate\Plugin\migrate;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Bean Migrate's deriver classes.
 *
 * @package Drupal\bean_migrate\Plugin\migrate
 */
trait BeanDeriverTrait {

  /**
   * Returns the migrations IDs which should get the type derivative ID.
   *
   * @return string[]
   *   An array of migration IDs.
   */
  protected static function getMigrationDependencyBaseIdsToFinalize(): array {
    return static::MIGRATION_BASE_IDS_TO_DERIVE;
  }

  /**
   * Returns the migrations IDs which should get the type derivative ID.
   *
   * @return string[]
   *   An array of migration IDs.
   */
  protected static function getMigrationLookupBaseIdsToFinalize(): array {
    return static::MIGRATION_BASE_IDS_TO_DERIVE;
  }

  /**
   * Finalizes the migration dependencies of a migration derivative.
   *
   * @param array $definition
   *   The migration plugin definition whose dependencies need to be finalized.
   * @param string $derivative_id
   *   The derivative ID to add.
   * @param string[]|null $migrations_to_finalize
   *   The migrations dependencies which should get the given derivative ID as
   *   suffix.
   */
  protected static function finalizeMigrationDependencies(array &$definition, string $derivative_id, $migrations_to_finalize = NULL): void {
    if (
      !isset($definition['migration_dependencies']['required']) &&
      !isset($definition['migration_dependencies']['optional'])
    ) {
      return;
    }

    if (!is_array($migrations_to_finalize)) {
      $migrations_to_finalize = static::getMigrationDependencyBaseIdsToFinalize();
    }

    foreach (array_keys($definition['migration_dependencies']) as $dependency_type) {
      foreach ($migrations_to_finalize as $migration_base_id) {
        $requirement_key = array_search($migration_base_id, $definition['migration_dependencies'][$dependency_type]);
        if ($requirement_key === FALSE) {
          continue;
        }

        $definition['migration_dependencies'][$dependency_type][$requirement_key] = implode(PluginBase::DERIVATIVE_SEPARATOR, [
          $migration_base_id,
          $derivative_id,
        ]);
      }

    }
  }

  /**
   * Updates non-derived migrations used in "migration_lookup" plugins.
   *
   * @param array $migration_definition
   *   A migration plugin definition to process.
   * @param string $derivative_id
   *   The derivative ID.
   * @param string[]|null $migrations_to_finalize
   *   The migrations which should get the given derivative ID as suffix.
   */
  protected static function updateMigrationLookups(array &$migration_definition, string $derivative_id, $migrations_to_finalize = NULL): void {
    if (!is_array($migrations_to_finalize)) {
      $migrations_to_finalize = static::getMigrationLookupBaseIdsToFinalize();
    }

    foreach ($migration_definition['process'] as &$process_configs) {
      if (!is_array($process_configs)) {
        continue;
      }

      if (isset($process_configs['plugin']) && $process_configs['plugin'] === 'migration_lookup' && isset($process_configs['migration'])) {
        static::updatePluginConfiguration($process_configs, $migrations_to_finalize, $derivative_id);
      }
      else {
        foreach ($process_configs as &$process_config) {
          if (!is_array($process_config)) {
            continue;
          }

          if (isset($process_config['plugin']) && $process_config['plugin'] === 'migration_lookup' && isset($process_config['migration'])) {
            static::updatePluginConfiguration($process_config, $migrations_to_finalize, $derivative_id);
          }
        }
      }
    }
  }

  /**
   * Updates a "migration_lookup"'s "migration" config with a derivative ID.
   *
   * Adds the given derivative ID to the given migrations.
   *
   * @param array $process_plugin_config
   *   A process plugin configuration.
   * @param string[] $migrations_to_finalize
   *   The migrations which should get the given derivative ID.
   * @param string $derivative_id
   *   The derivative ID.
   */
  private static function updatePluginConfiguration(array &$process_plugin_config, array $migrations_to_finalize, string $derivative_id): void {
    // The "migration" configuration can be a single migration plugin ID
    // (a string), or an array of migration plugin IDs.
    if (is_string($process_plugin_config['migration']) && in_array($process_plugin_config['migration'], $migrations_to_finalize, TRUE)) {
      $process_plugin_config['migration'] = implode(PluginBase::DERIVATIVE_SEPARATOR, [
        $process_plugin_config['migration'],
        $derivative_id,
      ]);
    }
    elseif (is_array($process_plugin_config['migration'])) {
      foreach ($process_plugin_config['migration'] as $delta => $original_migration) {
        if (!in_array($original_migration, $migrations_to_finalize, TRUE)) {
          continue;
        }

        $process_plugin_config['migration'][$delta] = implode(PluginBase::DERIVATIVE_SEPARATOR, [
          $original_migration,
          $derivative_id,
        ]);
      }
    }
  }

}
