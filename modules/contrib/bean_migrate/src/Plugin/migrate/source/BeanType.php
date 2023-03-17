<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Migration source plugin for Bean types.
 *
 * @MigrateSource(
 *   id = "bean_type",
 *   source_module = "bean"
 * )
 */
class BeanType extends DrupalSqlBaseWithCountCompatibility {

  /**
   * {@inheritdoc}
   *
   * @todo Reconsider after count caching is fixed in the base classes.
   * @see https://drupal.org/i/3190815
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    $configuration += [
      'type' => NULL,
      'cache_key' => hash('sha256', $plugin_id . serialize($configuration)),
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $bean_type_exists = $this->getDatabase()->schema()->tableExists('bean_type');
    $query = $this->select('bean')
      ->fields('bean', ['type'])
      ->groupBy('bean.type')
      ->orderBy('bean.type');

    if ($bean_type = $this->configuration['type'] ?? NULL) {
      $query->condition('bean.type', $bean_type);
    }

    // We might have an admin label and a description, but nothing guarantees
    // that every type has a record in the "bean_type" table.
    if ($bean_type_exists) {
      $query->leftJoin('bean_type', 'bt', 'bt.name = bean.type');
      $query->fields('bt', ['label', 'description'])
        ->groupBy('bt.label')
        ->groupBy('bt.description');
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'type' => $this->t('The type (machine name) of the bean type.'),
      'label' => $this->t('Label of the bean type, if available.'),
      'description' => $this->t('The description of the bean type, if available.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'type' => [
        'type' => 'string',
        'max_length' => 32,
        'alias' => 'bean',
      ],
    ];
  }

}
