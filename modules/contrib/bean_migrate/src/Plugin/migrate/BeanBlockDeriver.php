<?php

namespace Drupal\bean_migrate\Plugin\migrate;

use Drupal\bean_migrate\Plugin\migrate\source\BeanBlockPlacement;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Row;

/**
 * Deriver class for Bean block placement migration.
 */
class BeanBlockDeriver extends DeriverBase {

  use BeanDeriverTrait;
  use StringTranslationTrait;

  /**
   * Array of the bean migration plugin base IDs which are derived.
   *
   * @const string[]
   */
  const MIGRATION_BASE_IDS_TO_DERIVE = [
    'bean',
    'bean_block',
  ];

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $source = static::getSourcePlugin($base_plugin_definition['source']);
    if (!($source instanceof BeanBlockPlacement)) {
      throw new \LogicException(sprintf("'%s' should only used for Bean block placement migrations. The current migration definition's source plugin is a '%s' instance. The definition's base ID is '%s'.", get_class($this), get_class($source), $base_plugin_definition['id']));
    }
    try {
      $source->checkRequirements();
    }
    catch (RequirementsException $e) {
      return $this->derivatives;
    }

    try {
      foreach ($source as $row) {
        assert($row instanceof Row);
        $type = $row->getSourceProperty('type');
        if (array_key_exists($type, $this->derivatives)) {
          continue;
        }

        $derivative_id = $type;
        $derivative_definition = $base_plugin_definition;
        $derivative_definition['source']['type'] = $type;
        // Re-using the string used in other migration derivers.
        // @see \Drupal\node\Plugin\migrate\D7NodeDeriver
        $derivative_definition['label'] = $this->t('@label (@type)', [
          '@label' => $derivative_definition['label'],
          '@type' => $row->getSourceProperty('type_label') ?? $type,
        ]);

        // Finalize migration dependencies.
        static::finalizeMigrationDependencies($derivative_definition, $type);
        static::updateMigrationLookups($derivative_definition, $type);

        $this->derivatives[$derivative_id] = $derivative_definition;
      }
    }
    catch (DatabaseExceptionWrapper $e) {
    }

    return $this->derivatives;
  }

  /**
   * Returns a fully initialized instance of a source plugin with config.
   *
   * @param string|array $source_config
   *   The configuration of the source plugin, or the ID of the source plugin.
   *
   * @return \Drupal\migrate\Plugin\MigrateSourceInterface|\Drupal\migrate\Plugin\RequirementsInterface
   *   The fully initialized source plugin.
   */
  public static function getSourcePlugin($source_config) {
    if (is_string($source_config)) {
      $source_config = ['plugin' => $source_config];
    }
    $source_config = [
      'ignore_map' => TRUE,
    ] + $source_config;
    $definition = [
      'source' => $source_config,
      'destination' => [
        'plugin' => 'null',
      ],
      'idMap' => [
        'plugin' => 'null',
      ],
    ];
    return \Drupal::service('plugin.manager.migration')->createStubMigration($definition)->getSourcePlugin();
  }

}
