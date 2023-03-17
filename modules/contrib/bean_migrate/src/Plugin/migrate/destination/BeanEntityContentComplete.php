<?php

namespace Drupal\bean_migrate\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityContentComplete;
use Drupal\migrate\Row;

/**
 * Provides a destination for migrating beans.
 *
 * @MigrateDestination(
 *   id = "entity_complete:block_content",
 *   destination_module = "block_content"
 * )
 */
class BeanEntityContentComplete extends EntityContentComplete {

  /**
   * {@inheritdoc}
   */
  protected function getEntity(Row $row, array $old_destination_id_values) {
    $entity = parent::getEntity($row, $old_destination_id_values);

    // A newer revision of a bean content entity isn't always the default
    // revision.
    $is_default_revision = $row->getDestinationProperty('revision_default');
    if ($is_default_revision !== NULL) {
      $entity->isDefaultRevision($is_default_revision);
    }

    return $entity;
  }

}
