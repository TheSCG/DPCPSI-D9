<?php

namespace Drupal\bean_migrate\Plugin\migrate\process;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\migrate\process\MakeUniqueBase;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migrate process plugin for bean types.
 *
 * This plugin ensures that preexisting block content types, or
 * the "basic" content type created by the "block_content_type" migration won't
 * get overridden with types migrated from bean types.
 *
 * @MigrateProcessPlugin(
 *   id = "bean_unique_type"
 * )
 */
class BeanUniqueType extends MakeUniqueBase implements ContainerFactoryPluginInterface {

  /**
   * List of the preexisting block_content_type entities.
   *
   * @var string[]
   */
  protected $blockContentTypes;

  /**
   * The migration plugin manager.
   *
   * @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface
   */
  protected $migrationPluginManager;

  /**
   * Constructs a BeanBundle instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The block content type storage.
   * @param \Drupal\migrate\Plugin\MigrationPluginManagerInterface $migration_plugin_manager
   *   The migration plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $storage, MigrationPluginManagerInterface $migration_plugin_manager) {
    $configuration['postfix'] = '_bean_';
    $configuration['start'] = 0;
    $configuration['length'] = 32;

    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $preexisting_types = array_keys($storage->loadMultiple());
    try {
      $core_type_migration = $migration_plugin_manager->createInstance('block_content_type');
      assert($core_type_migration instanceof Migration);
      $core_type_migration->checkRequirements();
      $preexisting_types = array_unique(
        array_merge(
          ['basic'],
          $preexisting_types
        )
      );
    }
    catch (PluginException $e) {
    }
    catch (RequirementsException $e) {
    }

    $this->blockContentTypes = $preexisting_types;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('block_content_type'),
      $container->get('plugin.manager.migration')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function exists($value) {
    return in_array($value, $this->blockContentTypes);
  }

}
