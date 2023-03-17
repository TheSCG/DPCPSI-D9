<?php

namespace Drupal\views_migration\Plugin\migrate\views\exposed_form\d7;

use Drupal\views_migration\Plugin\migrate\views\MigrateViewsPluginPluginBase;

/**
 * The default Migrate Views Exposed Form plugin.
 *
 * This plugin is used to prepare the Views `exposed_form` display options for
 * migration when no other migrate plugin exists for the current exposed_form
 * plugin.
 *
 * @MigrateViewsExposedForm(
 *   id = "better_exposed_filters",
 *   plugin_ids = {
 *     "better_exposed_filters"
 *   },
 *   core = {7},
 * )
 */
class BetterExposedFilterForm extends MigrateViewsPluginPluginBase {

  /**
   * {@inheritdoc}
   */
  public function prepareDisplayOptions(array &$display_options) {
    $display_options['exposed_form']['type'] = 'bef';
    if (isset($display_options['exposed_form']['type']) && in_array($display_options['exposed_form']['type'], $this->pluginList['exposed_form'], TRUE)) {
      $display_options['exposed_form']['type'] = 'bef';
      $options = $display_options['exposed_form']['options'];
      $options['bef']['general']['autosubmit'] = $options['autosubmit'];
      $options['bef']['general']['autosubmit'] = $options['autosubmit_hide'];
      if (isset($options['bef']['general']['text_input_required'])) {
        if (isset($options['bef']['general']['text_input_required']['text_input_required']['value'])) {
          $options['text_input_required'] = $options['bef']['general']['text_input_required']['text_input_required']['value'];
          $options['text_input_required_format'] = $options['bef']['general']['text_input_required']['text_input_required']['format'];
        }
        unset($options['bef']['general']['text_input_required']);
      }
      $options['bef']['general']['secondary_open'] = $options['bef']['general']['secondary_collapse_override'];
      if ($options['bef']['general']['secondary_collapse_override'] == 2) {
        $options['bef']['general']['secondary_open'] = FALSE;
      }
      unset($options['autosubmit']);
      unset($options['autosubmit_hide']);
      $filters = [];
      foreach ($options['bef'] as $key => $filter) {
        if ($key != 'general') {
          unset($filter['bef_format']);
          $moreOptions = $filter['more_options'];
          $filter['plugin_id'] = 'bef';
          if (isset($moreOptions['bef_select_all_none'])) {
            $filter['select_all_none'] = $moreOptions['bef_select_all_none'];
          }
          $filter['advanced'] = [];
          if (isset($moreOptions['bef_collapsible'])) {
            $filter['advanced']['collapsible'] = $moreOptions['bef_collapsible'];
          }
          $filter['advanced']['is_secondary'] = $moreOptions['is_secondary'];
          $filter['advanced']['rewrite'] = $moreOptions['rewrite'];
          unset($filter['more_options']);
          $filters[$key] = $filter;
          unset($options['bef'][$key]);
        }
      }
      $options['bef']['filter'] = $filters;
      $display_options['exposed_form']['options'] = $options;
    }
  }

}
