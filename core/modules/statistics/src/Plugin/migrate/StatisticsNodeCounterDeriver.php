<?php

namespace Drupal\statistics\Plugin\migrate;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationDeriverTrait;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Deriver for Drupal 7 statistics node counter migrations based on node types.
 *
 * @see \Drupal\node\Plugin\migrate\D7NodeDeriver
 */
class StatisticsNodeCounterDeriver extends DeriverBase {

  use MigrationDeriverTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $source = static::getSourcePlugin('node_counter');
    assert($source instanceof DrupalSqlBase);

    try {
      $source->checkRequirements();
    }
    catch (RequirementsException $e) {
      // Nothing to generate.
      return $this->derivatives;
    }

    try {
      $used_node_types_query = $source->getDatabase()->select('node', 'n')
        ->fields('n', ['type'])
        ->groupBy('n.type');
      $used_node_types_query->join($source->query(), 'ncq', '[n].[nid] = [ncq].[nid]');
      $node_types = array_keys(
        $used_node_types_query
          ->execute()
          ->fetchAllAssoc('type', \PDO::FETCH_ASSOC)
      );

      foreach ($node_types as $node_type) {
        $derivative_definition = $base_plugin_definition;
        $dependency_index = array_search('d7_node', $derivative_definition['migration_dependencies']['optional']);
        if ($dependency_index !== FALSE) {
          $derivative_definition['migration_dependencies']['optional'][$dependency_index] .= ":$node_type";
        }
        $derivative_definition['source']['node_type'] = $node_type;
        $this->updateNodeMigrationLookups($derivative_definition);
        $this->derivatives[$node_type] = $derivative_definition;
      }
    }
    catch (DatabaseExceptionWrapper $e) {
      // Once we begin iterating the source plugin it is possible that the
      // source tables will not exist. This can happen when the
      // MigrationPluginManager gathers up the migration definitions but we do
      // not actually have a Drupal 7 source database.
    }

    return $this->derivatives;
  }

  /**
   * Updates d7_node and d7_node_complete migrations used in lookup plugins.
   *
   * @param array $migration_definition
   *   A migration plugin definition to process.
   */
  protected function updateNodeMigrationLookups(array &$migration_definition): void {
    $node_type = $migration_definition['source']['node_type'] ?? NULL;
    if (!$node_type) {
      return;
    }

    foreach ($migration_definition['process'] as &$process_configs) {
      if (!is_array($process_configs)) {
        continue;
      }

      if (isset($process_configs['plugin']) && $process_configs['plugin'] === 'migration_lookup' && isset($process_configs['migration'])) {
        $this->processPluginDefinition($process_configs, $node_type);
      }
      else {
        foreach ($process_configs as &$process_config) {
          if (!is_array($process_config)) {
            continue;
          }

          if (isset($process_config['plugin']) && $process_config['plugin'] === 'migration_lookup' && isset($process_config['migration'])) {
            $this->processPluginDefinition($process_config, $node_type);
          }
        }
      }
    }
  }

  /**
   * Updates "d7_node" and "d7_node_complete" lookups with a derivative ID.
   *
   * @param array $process_plugin_config
   *   A process plugin configuration.
   * @param string $node_type
   *   The node bundle which should be added as derivative ID.
   */
  protected function processPluginDefinition(array &$process_plugin_config, string $node_type): void {
    $migration_lookup_map = [
      'd7_node' => implode(PluginBase::DERIVATIVE_SEPARATOR, [
        'd7_node',
        $node_type,
      ]),
      'd7_node_complete' => implode(PluginBase::DERIVATIVE_SEPARATOR, [
        'd7_node_complete',
        $node_type,
      ]),
    ];

    // Migration can be a single migration plugin ID (aka string), or an
    // array of migration plugin IDs.
    if (is_string($process_plugin_config['migration']) && isset($migration_lookup_map[$process_plugin_config['migration']])) {
      $process_plugin_config['migration'] = $migration_lookup_map[$process_plugin_config['migration']];
    }
    elseif (is_array($process_plugin_config['migration'])) {
      foreach ($migration_lookup_map as $original_migration_plugin_id => $derived_plugin_id) {
        $lookup_index = array_search($original_migration_plugin_id, $process_plugin_config['migration']);

        if ($lookup_index !== FALSE) {
          $process_plugin_config['migration'][$lookup_index] = $derived_plugin_id;
        }
      }
    }
  }

}
