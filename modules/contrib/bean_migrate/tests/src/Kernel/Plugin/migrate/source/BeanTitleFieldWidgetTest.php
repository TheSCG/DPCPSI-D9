<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

/**
 * Tests the "bean_title_field_widget" migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanTitleFieldWidget
 * @group bean_migrate
 */
class BeanTitleFieldWidgetTest extends BeanSourceTestBase {

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    $test_cases = [
      'All bundles' => [
        'Source' => [
          'bean' => [
            ['type' => 'simple'],
            ['type' => 'complicated'],
            ['type' => 'complicated'],
            ['type' => 'simple'],
          ],
          'variable' => [
            [
              'name' => 'field_bundle_settings_bean__simple',
              'value' => serialize([
                'extra_fields' => ['form' => ['title' => ['weight' => '-5']]],
              ]),
            ],
            [
              'name' => 'field_bundle_settings_bean__complicated',
              'value' => serialize([
                'extra_fields' => ['form' => ['title' => ['weight' => '44']]],
              ]),
            ],
          ],
        ],
        'Expected' => [
          [
            'type' => 'complicated',
            'widget_weight' => 44,
            'cache_key' => 'd8b9d6900126d58ef6415ef70b326e29fe82221cc2ccfad3aca0f8e4f7d114fa',
          ],
          [
            'type' => 'simple',
            'widget_weight' => -5,
            'cache_key' => 'd8b9d6900126d58ef6415ef70b326e29fe82221cc2ccfad3aca0f8e4f7d114fa',
          ],
        ],
      ],
    ];
    $test_cases['Filtering for "simple"'] = [
      'Source' => $test_cases['All bundles']['Source'],
      'Expected' => [
        [
          'type' => 'simple',
          'widget_weight' => -5,
          'cache_key' => '61635ccb922dbce99a23e665273b01a6931159ee1175602ae5114f46926a304f',
        ],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'type' => 'simple',
      ],
    ];
    $test_cases['Filtering for a missing type'] = [
      'Source' => $test_cases['All bundles']['Source'],
      'Expected' => [],
      'Count' => NULL,
      'Plugin config' => [
        'type' => 'missing_type',
      ],
    ];

    return $test_cases;
  }

}
