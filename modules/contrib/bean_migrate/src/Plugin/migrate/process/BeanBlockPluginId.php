<?php

namespace Drupal\bean_migrate\Plugin\migrate\process;

use Drupal\block\Plugin\migrate\process\BlockPluginId;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Migrate process plugin for block configs using bean content entities.
 *
 * @MigrateProcessPlugin(
 *   id = "bean_block_plugin_id"
 * )
 */
class BeanBlockPluginId extends BlockPluginId {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      return NULL;
    }

    if (count($value) !== 2) {
      return NULL;
    }

    $lookup_result = $this->migrateLookup->lookup(['bean'], $value);
    if ($lookup_result) {
      $block_id = $lookup_result[0]['id'];
    }

    if (!empty($block_id)) {
      $uuid = $this->blockContentStorage->load($block_id)->uuid();
      return "block_content:{$uuid}";
    }

    return NULL;
  }

}
