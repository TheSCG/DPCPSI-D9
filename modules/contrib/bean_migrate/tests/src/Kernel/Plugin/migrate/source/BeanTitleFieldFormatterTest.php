<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

/**
 * Tests the "bean_title_field_formatter" migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanTitleFieldFormatter
 * @group bean_migrate
 */
class BeanTitleFieldFormatterTest extends BeanSourceTestBase {

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
            ['type' => 'complicated'],
            ['type' => 'simple'],
          ],
          'variable' => [
            [
              'name' => 'field_bundle_settings_bean__simple',
              'value' => serialize([
                'extra_fields' => [
                  'display' => [
                    'title' => [
                      'default' => [
                        'weight' => '-10',
                        'visible' => TRUE,
                      ],
                      'card' => [
                        'weight' => '11',
                        'visible' => FALSE,
                      ],
                    ],
                  ],
                ],
              ]),
            ],
            [
              'name' => 'field_bundle_settings_bean__complicated',
              'value' => serialize([
                'extra_fields' => [
                  'display' => [
                    'title' => [
                      'default' => [
                        'weight' => '-5',
                        'visible' => TRUE,
                      ],
                    ],
                  ],
                ],
              ]),
            ],
          ],
        ],
        'Expected' => [
          [
            'type' => 'complicated',
            'view_mode' => 'default',
            'field_display_weight' => -5,
            'field_display_is_hidden' => FALSE,
            'cache_key' => '453f7013bc4ea6acb9358f3f5eda10f8345b83845dd7c23467cff32bec9cc299',
          ],
          [
            'type' => 'simple',
            'view_mode' => 'default',
            'field_display_weight' => -10,
            'field_display_is_hidden' => FALSE,
            'cache_key' => '453f7013bc4ea6acb9358f3f5eda10f8345b83845dd7c23467cff32bec9cc299',
          ],
          [
            'type' => 'simple',
            'view_mode' => 'card',
            'field_display_weight' => 11,
            'field_display_is_hidden' => TRUE,
            'cache_key' => '453f7013bc4ea6acb9358f3f5eda10f8345b83845dd7c23467cff32bec9cc299',
          ],
        ],
      ],
    ];
    $test_cases['Filtering for "complicated"'] = [
      'Source' => $test_cases['All bundles']['Source'],
      'Expected' => [
        [
          'type' => 'complicated',
          'view_mode' => 'default',
          'field_display_weight' => -5,
          'field_display_is_hidden' => FALSE,
          'cache_key' => '0b144540edfe866516f132f0178c848997fe111524476274e73f2297d67e3f63',
        ],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'type' => 'complicated',
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
