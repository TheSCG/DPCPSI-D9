<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

/**
 * Migration source plugin for Bean title field widget settings.
 *
 * @MigrateSource(
 *   id = "bean_title_field_widget",
 *   source_module = "bean"
 * )
 */
class BeanTitleFieldWidget extends BeanType {

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $iterator_rows = [];
    foreach (parent::initializeIterator() as $item) {
      $field_bundle_settings = $this->variableGet("field_bundle_settings_bean__{$item['type']}", []);
      $widget_weight = $field_bundle_settings['extra_fields']['form']['title']['weight'] ?? -10;
      $iterator_rows[] = $item + [
        'widget_weight' => (int) $widget_weight,
      ];
    }

    return new \ArrayIterator($iterator_rows);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() + [
      'widget_weight' => $this->t("The weight of the title field's widget on the entity's default form display."),
    ];
  }

}
