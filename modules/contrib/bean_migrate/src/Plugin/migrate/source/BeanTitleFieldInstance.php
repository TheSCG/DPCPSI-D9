<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

/**
 * Migration source plugin for Bean title field instances.
 *
 * @MigrateSource(
 *   id = "bean_title_field_instance",
 *   source_module = "bean"
 * )
 */
class BeanTitleFieldInstance extends BeanType {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    $query->leftJoin('field_config_instance', 'fci', 'fci.field_name = :title_field_name AND fci.entity_type = :entity_type AND fci.bundle = bean.type', [
      ':title_field_name' => 'title_field',
      ':entity_type' => 'bean',
    ]);
    $query->addExpression('CASE WHEN fci.entity_type = :exp_entity_type THEN 1 ELSE 0 END', 'title_field_exists', [
      ':exp_entity_type' => 'bean',
    ]);

    $query->groupBy('fci.entity_type');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() + [
      'title_field_exists' => $this->t('Whether the title was replaced with a field by the Title module.'),
    ];
  }

}
