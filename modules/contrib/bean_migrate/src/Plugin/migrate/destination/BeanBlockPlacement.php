<?php

namespace Drupal\bean_migrate\Plugin\migrate\destination;

use Drupal\block\Plugin\migrate\destination\EntityBlock;
use Drupal\migrate\Row;

/**
 * Destination plugin for Bean block placements.
 *
 * @MigrateDestination(
 *   id = "entity_bean_block"
 * )
 */
class BeanBlockPlacement extends EntityBlock {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'block';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityId(Row $row) {
    // Block placement translation can be easily identified by block plugin ID.
    // @see \Drupal\migrate\Plugin\migrate\destination\Entity::getEntity()
    // @see \Drupal\migrate\Plugin\migrate\destination\EntityConfigBase::updateEntity()
    if (
      $row->getDestinationProperty('langcode') &&
      !empty($id = $row->getDestinationProperty('id'))
    ) {
      return $id;
    }

    return parent::getEntityId($row);
  }

}
