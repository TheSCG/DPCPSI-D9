<?php

namespace Drupal\bean_migrate\Plugin\migrate;

use Drupal\bean_migrate\Plugin\migrate\source\Bean;
use Drupal\bean_migrate\Plugin\migrate\source\BeanType;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationDeriverTrait;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\FieldDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deriver class for Bean migrations based on bean types.
 */
class BeanDeriver extends DeriverBase implements ContainerDeriverInterface {

  use BeanDeriverTrait;
  use MigrationDeriverTrait;
  use StringTranslationTrait;

  /**
   * Array of the bean migration plugin base IDs which are derived.
   *
   * @const string[]
   */
  const MIGRATION_BASE_IDS_TO_DERIVE = [
    'bean',
    'bean_type',
    'bean_langcode_field_widget',
    'bean_title_field_formatter',
    'bean_title_field_instance',
    'bean_title_field_widget',
    'bean_translation_settings',
  ];

  /**
   * The base plugin ID this derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The migration field discovery service.
   *
   * @var \Drupal\migrate_drupal\FieldDiscoveryInterface
   */
  protected $fieldDiscovery;

  /**
   * D7NodeDeriver constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID for the plugin ID.
   * @param \Drupal\migrate_drupal\FieldDiscoveryInterface $field_discovery
   *   The migration field discovery service.
   */
  public function __construct($base_plugin_id, FieldDiscoveryInterface $field_discovery) {
    $this->basePluginId = $base_plugin_id;
    $this->fieldDiscovery = $field_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('migrate_drupal.field_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $source = static::getSourcePlugin($base_plugin_definition['source']['plugin']);
    if (
      !($source instanceof BeanType) &&
      !($source instanceof Bean)
    ) {
      throw new \LogicException(sprintf('"%s" should only used for Bean related migrations. The current migration definition\'s source plugin is a "%s" instance. The definition\s base ID: "%s".', get_class($this), get_class($source), $base_plugin_definition['id']));
    }

    try {
      $source->checkRequirements();
    }
    catch (RequirementsException $e) {
      return $this->derivatives;
    }

    $is_bean_entity_derivative = $source instanceof Bean;

    try {
      foreach ($source as $row) {
        assert($row instanceof Row);
        $type = $row->getSourceProperty('type');
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

        // Only add field value processes to the derivative definition if this
        // is a Bean content entity migration derivative.
        if ($is_bean_entity_derivative) {
          $migration_plugin_manager = \Drupal::service('plugin.manager.migration');
          assert($migration_plugin_manager instanceof MigrationPluginManagerInterface);
          $migration = $migration_plugin_manager->createStubMigration($derivative_definition);
          $this->fieldDiscovery->addBundleFieldProcesses($migration, 'bean', $type);
          $derivative_definition = $migration->getPluginDefinition();
        }

        static::updateMigrationLookups($derivative_definition, $type);

        $this->derivatives[$derivative_id] = $derivative_definition;
      }
    }
    catch (DatabaseExceptionWrapper $e) {
    }

    return $this->derivatives;
  }

}
