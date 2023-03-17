<?php

declare(strict_types=1);

namespace Drupal\bean_migrate;

use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationDeriverTrait;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Contains methods which makes Bean Migrate compatible with other migrations.
 */
class MigrationPluginAlterer {

  use MigrationDeriverTrait {
    getSourcePlugin as private;
  }

  /**
   * Makes field instance migration compatible with bean.
   *
   * This method adds the "bean_type" migration as dependency to the field
   * instance migration.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function alterFieldInstanceMigrations(array &$migration_definitions): void {
    $field_instance_source = static::getSourcePlugin('d7_field_instance');
    assert($field_instance_source instanceof DrupalSqlBase);

    try {
      $field_instance_source->checkRequirements();
    }
    catch (RequirementsException $e) {
      return;
    }

    $bean_fields = (int) $field_instance_source->query()
      ->condition('fci.entity_type', 'bean')
      ->countQuery()->execute()->fetchField();

    if (!$bean_fields) {
      // There aren't any bean field instances in the source DB.
      return;
    }

    $field_instance_migrations = array_filter($migration_definitions, function ($definition) {
      return $definition['id'] === 'd7_field_instance';
    });

    foreach ($field_instance_migrations as $migration_plugin_id => $definition) {
      if (
        empty($definition['source']['entity_type']) ||
        $definition['source']['entity_type'] === 'bean'
      ) {
        $migration_definitions[$migration_plugin_id]['migration_dependencies']['optional'][] = 'bean_type';
      }
    }
  }

  /**
   * Removes the block ID process from "d7_custom_block" migrations.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function fixBlockContentMigrations(array &$migration_definitions): void {
    // Custom block translations are using migration_lookup for determining the
    // block ID, so we only have to fix the default "d7_custom_block" migration.
    // The "block_plugin_id" migration process plugin also uses a migration
    // lookup internally.
    // @see \Drupal\block\Plugin\migrate\process\BlockPluginId
    if (empty($migration_definitions['d7_custom_block'])) {
      return;
    }
    unset($migration_definitions['d7_custom_block']['process']['id']);
  }

  /**
   * Keeps the block region mapping up-to-date with core block migration.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function copyCoreBlockRegionMappingToBeanBlockPlacement(array &$migration_definitions): void {
    $core_block_migrations = array_filter($migration_definitions, function ($definition) {
      return $definition['id'] === 'd7_block';
    });
    if (empty($core_block_migrations)) {
      return;
    }
    $core_block_migration = reset($core_block_migrations);

    $bean_block_migration_ids = array_keys(
      array_filter($migration_definitions, function ($definition) {
        return $definition['id'] === 'bean_block';
      })
    );
    if (empty($bean_block_migration_ids)) {
      return;
    }

    foreach ($bean_block_migration_ids as $migration_id) {
      $migration_definitions[$migration_id]['process']['region'] = $core_block_migration['process']['region'];
    }
  }

  /**
   * Maps view mode and field migrations from bean entity type to block content.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function mapBeanToBlockContent(array &$migration_definitions): void {
    $bean_map_tag = 'Bean map processed';
    $d7_field_view_mode_migrations = array_filter($migration_definitions, function (array $definition) use ($bean_map_tag) {
      $migration_tags = $definition['migration_tags'] ?? [];
      $source_plugin_id = $definition['source']['plugin'] ?? '';
      return !in_array($bean_map_tag, $migration_tags) &&
        in_array('Drupal 7', $migration_tags) &&
        in_array($source_plugin_id, [
          'd7_field',
          'd7_field_instance',
          'd7_field_instance_label_description_translation',
          'd7_field_instance_per_form_display',
          'd7_field_instance_per_view_mode',
          'd7_field_option_translation',
          'd7_view_mode',
        ]);
    });

    foreach ($d7_field_view_mode_migrations as $plugin_id => $plugin_definition) {
      $migration_definitions[$plugin_id]['migration_tags'][] = $bean_map_tag;

      // Map entity type.
      foreach (['targetEntityType', 'entity_type'] as $destination_property) {
        if (!isset($plugin_definition['process'][$destination_property])) {
          continue;
        }

        $entity_type_process = self::makeProcessAssociative($plugin_definition['process'][$destination_property]);
        $entity_type_process[] = [
          'plugin' => 'static_map',
          'map' => ['bean' => 'block_content'],
          'bypass' => TRUE,
        ];
        $migration_definitions[$plugin_id]['process'][$destination_property] = $entity_type_process;
        $plugin_definition = $migration_definitions[$plugin_id];
      }

      // Map bundle if present.
      if (!isset($plugin_definition['process']['bundle'])) {
        // Bundle property isn't present or isn't used.
        continue;
      }

      // We need a preceding value process that looks for the appropriate
      // destination block_content type when the entity type is bean.
      $bundle_process_index = array_search('bundle', array_keys($plugin_definition['process']));
      $processes_before_bundle = array_slice($plugin_definition['process'], 0, $bundle_process_index);
      $processes_after_bundle = array_slice($plugin_definition['process'], $bundle_process_index + 1);
      $original_bundle_process = $plugin_definition['process']['bundle'];
      $bean_bundle_lookup_process = [
        [
          'plugin' => 'bean_compare',
          'source' => [
            'entity_type',
            'constants/bean',
          ],
        ],
        [
          'plugin' => 'skip_on_empty',
          'method' => 'process',
        ],
        [
          'plugin' => 'migration_lookup',
          'migration' => 'bean_type',
          'no_stub' => TRUE,
          'source' => 'bundle',
        ],
        [
          'plugin' => 'skip_on_empty',
          'method' => 'process',
        ],
      ];
      $new_bundle_process = [
        'plugin' => 'null_coalesce',
        'source' => [
          '@bean_bundle',
          '@original_bundle',
        ],
      ];
      $migration_definitions[$plugin_id]['source']['constants']['bean'] = 'bean';
      $migration_definitions[$plugin_id]['process'] =
        $processes_before_bundle +
        ['bean_bundle' => $bean_bundle_lookup_process] +
        ['original_bundle' => $original_bundle_process] +
        ['bundle' => $new_bundle_process] +
        $processes_after_bundle;
    }
  }

  /**
   * Finalizes view mode and field migration dependencies.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function finalizeDerivedFieldMigrations(array &$migration_definitions): void {
    $d7_migrations = array_filter($migration_definitions, function (array $definition) {
      return in_array('Drupal 7', $definition['migration_tags'] ?? []);
    });
    $bean_migrations = array_filter($d7_migrations, function (array $definition) {
      return !empty($definition['provider']) && $definition['provider'] === 'bean_migrate';
    });

    // View mode and field storage migrations might be derived only per entity
    // type, so we acquire only the migration plugin ID of the bean related
    // derivative.
    $derived_per_entity_type = [];
    foreach (['d7_view_modes', 'd7_field'] as $base_migration_id) {
      $derived_migration_ids_for_bean = array_keys(
        array_filter($d7_migrations, function (array $definition) use ($base_migration_id) {
          return $definition['id'] === $base_migration_id &&
            !empty($definition['source']['entity_type']) &&
            $definition['source']['entity_type'] === 'bean';
        })
      );

      if (!empty($derived_migration_ids_for_bean)) {
        assert(count($derived_migration_ids_for_bean) === 1);
        $derived_per_entity_type[$base_migration_id] = reset($derived_migration_ids_for_bean);
      }
    }

    // Field instance, field widget and field formatter migrations might be
    // derived per entity type and per bundle. We collect full derivative
    // definitions.
    $derived_per_et_and_bundle = [];
    $base_migration_ids = [
      'd7_field_instance',
      'd7_field_formatter_settings',
      'd7_field_instance_widget_settings',
    ];
    foreach ($base_migration_ids as $base_migration_id) {
      $derived_migration_defs_for_bean = array_filter($d7_migrations, function (array $definition) use ($base_migration_id) {
        return $definition['id'] === $base_migration_id &&
          !empty($definition['source']['entity_type']) &&
          $definition['source']['entity_type'] === 'bean';
      });

      if (!empty($derived_migration_defs_for_bean)) {
        $derived_per_et_and_bundle[$base_migration_id] = $derived_migration_defs_for_bean;
      }
    }

    // Return when no derived field* migrations are present.
    if (empty($derived_per_entity_type) && empty($derived_per_et_and_bundle)) {
      return;
    }

    foreach ($bean_migrations as $bean_plugin_id => $bean_plugin_definition) {
      foreach (['required', 'optional'] as $dependency_type) {
        if (!($dependencies = $bean_plugin_definition['migration_dependencies'][$dependency_type] ?? NULL)) {
          continue;
        }

        // Finalize derived view mode and field storage migration dependencies.
        foreach ($derived_per_entity_type as $base_migration_id => $replacement_id) {
          $dep_key = array_search($base_migration_id, $dependencies, TRUE);
          if ($dep_key !== FALSE) {
            $dependencies[$dep_key] = $replacement_id;
          }
        }

        if (!($bean_type = $bean_plugin_definition['source']['type'] ?? NULL)) {
          $migration_definitions[$bean_plugin_id]['migration_dependencies'][$dependency_type] = $dependencies;
          continue;
        }

        // Derived field instance, field widget and field formatter
        // dependencies.
        foreach ($derived_per_et_and_bundle as $base_migration_id => $bean_bundle_derivatives) {
          $related_requirement_ids = array_keys(
            array_filter($bean_bundle_derivatives, function (array $bundle_derivative) use ($bean_type) {
              return !empty($bundle_derivative['source']['bundle']) &&
                $bundle_derivative['source']['bundle'] === $bean_type;
            })
          );

          if (!empty($related_requirement_ids)) {
            assert(count($related_requirement_ids) === 1);
            $dep_key = array_search($base_migration_id, $dependencies, TRUE);
            if ($dep_key !== FALSE) {
              $dependencies[$dep_key] = reset($related_requirement_ids);
            }
          }
        }

        $migration_definitions[$bean_plugin_id]['migration_dependencies'][$dependency_type] = $dependencies;
      }
    }
  }

  /**
   * Remove uuid destination process pipeline from bean migration if not needed.
   *
   * @param array $migration_definitions
   *   An associative array of migrations keyed by migration ID, as passed to
   *   hook_migration_plugins_alter().
   */
  public static function removeUuidProcessIfNotNeeded(array &$migration_definitions): void {
    $bean_source = static::getSourcePlugin('bean_type');
    assert($bean_source instanceof DrupalSqlBase);
    $bean_uuid_enabled = !empty($bean_source->getSystemData()['module']['bean_uuid']['status'] ?? NULL);
    if ($bean_uuid_enabled) {
      return;
    }

    $bean_migrations = array_filter(
      $migration_definitions,
      function (array $definition) {
        return $definition['source']['plugin'] === 'bean';
      }
    );

    foreach (array_keys($bean_migrations) as $plugin_id) {
      unset($migration_definitions[$plugin_id]['process']['uuid']);
    }
  }

  /**
   * Converts a destination process to an associative array.
   *
   * @param array|string $plugin_process
   *   The plugin process mapping.
   *
   * @return array[]
   *   The plugin process mapping as an associative array.
   */
  public static function makeProcessAssociative($plugin_process): array {
    if (!is_array($plugin_process)) {
      $plugin_process = [
        [
          'plugin' => 'get',
          'source' => $plugin_process,
        ],
      ];
    }
    elseif (array_key_exists('plugin', $plugin_process)) {
      $plugin_process = [$plugin_process];
    }

    return $plugin_process;
  }

}
