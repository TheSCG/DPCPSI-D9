<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

/**
 * Migration source plugin for Bean title field formatter settings.
 *
 * @MigrateSource(
 *   id = "bean_title_field_formatter",
 *   source_module = "bean"
 * )
 */
class BeanTitleFieldFormatter extends BeanType {

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $iterator_rows = [];
    foreach (parent::initializeIterator() as $item) {
      $field_bundle_settings = $this->variableGet("field_bundle_settings_bean__{$item['type']}", []);
      $display_settings = $field_bundle_settings['extra_fields']['display']['title'] ?? [];
      foreach ($display_settings as $view_mode => $title_settings) {
        $field_display_weight = $title_settings['weight'] ?? -10;
        $field_is_visible = $title_settings['visible'] ?? TRUE;

        $iterator_rows[] = $item + [
          'view_mode' => $view_mode,
          'field_display_weight' => (int) $field_display_weight,
          'field_display_is_hidden' => (bool) !$field_is_visible,
        ];
      }
    }

    return new \ArrayIterator($iterator_rows);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() + [
      'view_mode' => $this->t('The view mode associated with the title field display settings.'),
      'field_display_weight' => $this->t('The weight of the title field on the entity display.'),
      'field_display_is_hidden' => $this->t('Whether the title field is hidden in the current view mode.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return parent::getIds() + [
      'view_mode' => [
        'type' => 'string',
      ],
    ];
  }

}
