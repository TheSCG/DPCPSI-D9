<?php

namespace Drupal\statistics\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 6/7 node counter source from database.
 *
 * For available configuration keys, refer to the parent classes.
 *
 * @see \Drupal\migrate\Plugin\migrate\source\SqlBase
 * @see \Drupal\migrate\Plugin\migrate\source\SourcePluginBase
 *
 * @MigrateSource(
 *   id = "node_counter",
 *   source_module = "statistics"
 * )
 */
class NodeCounter extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('node_counter', 'nc')->fields('nc');
    if (isset($this->configuration['node_type'])) {
      $query->innerJoin('node', 'n', '[nc].[nid] = [n].[nid]');
      $query->condition('n.type', $this->configuration['node_type']);
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'nid' => $this->t('The node ID.'),
      'totalcount' => $this->t('The total number of times the node has been viewed.'),
      'daycount' => $this->t('The total number of times the node has been viewed today.'),
      'timestamp' => $this->t('The most recent time the node has been viewed.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'nc';
    return $ids;
  }

}
