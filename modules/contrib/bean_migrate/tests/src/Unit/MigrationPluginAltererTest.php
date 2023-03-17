<?php

namespace Drupal\Tests\bean_migrate\Unit;

use Drupal\bean_migrate\MigrationPluginAlterer;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Database\StatementInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\field\Plugin\migrate\source\d7\FieldInstance;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Tests Bean Migrate's MigrationPluginAlterer.
 *
 * @coversDefaultClass \Drupal\bean_migrate\MigrationPluginAlterer
 * @group bean_migrate
 */
class MigrationPluginAltererTest extends UnitTestCase {

  /**
   * Tests that no exception thrown ::alterFieldInstanceMigrations.
   */
  public function testAlterFieldInstanceMigrationsException() {
    $fci_source = $this->prophesize(FieldInstance::class);
    $fci_source->willImplement(RequirementsInterface::class);
    $fci_source->checkRequirements()->willThrow(RequirementsException::class);

    $fci_migration = $this->prophesize(Migration::class);
    $fci_migration->getSourcePlugin()->willReturn($fci_source->reveal());

    $migration_plugin_manager = $this->prophesize(MigrationPluginManager::class);
    $migration_plugin_manager->createStubMigration(Argument::any())->willReturn($fci_migration->reveal());

    $container = new ContainerBuilder();
    $container->set('plugin.manager.migration', $migration_plugin_manager->reveal());
    \Drupal::setContainer($container);

    $test_array = [0 => 'string'];
    MigrationPluginAlterer::alterFieldInstanceMigrations($test_array);
    $this->assertEquals([0 => 'string'], $test_array);
  }

  /**
   * Tests that field instance migrations are altered as expected.
   *
   * @param array[] $definitions
   *   An array of migration plugin definitions to test with.
   * @param int $bean_field_number
   *   The number of the bean fields discovered in the source database.
   * @param array[] $expected
   *   The expected results.
   *
   * @dataProvider providerTestAlterFieldInstanceMigrations
   * @covers ::alterFieldInstanceMigrations
   */
  public function testAlterFieldInstanceMigrations(array $definitions, int $bean_field_number, array $expected) {
    $statement = $this->prophesize(StatementInterface::class);
    $statement->fetchField()->willReturn((string) $bean_field_number);

    $fci_query = $this->prophesize(SelectInterface::class);
    $fci_query->condition(Argument::cetera())->willReturn($fci_query);
    $fci_query->countQuery()->willReturn($fci_query);
    $fci_query->execute()->willReturn($statement->reveal());

    $fci_source = $this->prophesize(FieldInstance::class);
    $fci_source->checkRequirements()->will(function () {});
    $fci_source->query()->willReturn($fci_query->reveal());

    $fci_migration = $this->prophesize(Migration::class);
    $fci_migration->getSourcePlugin()->willReturn($fci_source->reveal());

    $migration_plugin_manager = $this->prophesize(MigrationPluginManager::class);
    $migration_plugin_manager->createStubMigration(Argument::any())->willReturn($fci_migration->reveal());

    $container = new ContainerBuilder();
    $container->set('plugin.manager.migration', $migration_plugin_manager->reveal());
    \Drupal::setContainer($container);

    MigrationPluginAlterer::alterFieldInstanceMigrations($definitions);

    $this->assertEquals($expected, $definitions);
  }

  /**
   * Tests that fixing the custom block migration works as expected.
   *
   * @param array[] $definitions
   *   An array of migration plugin definitions to test with.
   * @param array[] $expected
   *   The expected results.
   *
   * @dataProvider providerTestFixBlockContentMigrations
   * @covers ::fixBlockContentMigrations
   */
  public function testFixBlockContentMigrations(array $definitions, array $expected) {
    MigrationPluginAlterer::fixBlockContentMigrations($definitions);
    $this->assertEquals($expected, $definitions);
  }

  /**
   * Tests that block region mapping is refreshed.
   *
   * @param array[] $definitions
   *   An array of migration plugin definitions to test with.
   * @param array[] $expected
   *   The expected results.
   *
   * @dataProvider providerTestCopyCoreBlockRegionMappingToBeanBlockPlacement
   * @covers ::copyCoreBlockRegionMappingToBeanBlockPlacement
   */
  public function testCopyCoreBlockRegionMappingToBeanBlockPlacement(array $definitions, array $expected) {
    MigrationPluginAlterer::copyCoreBlockRegionMappingToBeanBlockPlacement($definitions);
    $this->assertEquals($expected, $definitions);
  }

  /**
   * Tests that bean migration deps are updated with derived field migrations.
   *
   * @param array[] $definitions
   *   An array of migration plugin definitions to test with.
   * @param array[] $expected
   *   The expected result.
   *
   * @dataProvider providerTestFinalizeDerivedFieldMigrations
   * @covers ::finalizeDerivedFieldMigrations
   */
  public function testFinalizeDerivedFieldMigrations(array $definitions, array $expected) {
    MigrationPluginAlterer::finalizeDerivedFieldMigrations($definitions);
    $this->assertEquals($expected, $definitions);
  }

  /**
   * Data provider for ::testFinalizeDerivedFieldMigrations.
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestFinalizeDerivedFieldMigrations() {
    $d7_view_modes = [
      'id' => 'd7_view_modes',
      'migration_tags' => ['Drupal 7'],
      'source' => ['plugin' => 'd7_view_mode'],
    ];
    $d7_field = [
      'id' => 'd7_field',
      'migration_tags' => ['Drupal 7'],
      'source' => ['plugin' => 'd7_field'],
    ];
    $d7_field_instance = [
      'id' => 'd7_field_instance',
      'migration_tags' => ['Drupal 7'],
      'source' => ['plugin' => 'd7_field'],
    ];
    $d7_field_widget = [
      'id' => 'd7_field_instance_widget_settings',
      'migration_tags' => ['Drupal 7'],
      'source' => ['plugin' => 'd7_field_instance_per_form_display'],
    ];
    $d7_field_formatter = [
      'id' => 'd7_field_formatter_settings',
      'migration_tags' => ['Drupal 7'],
      'source' => ['plugin' => 'd7_field_instance_per_view_mode'],
    ];

    $bean_migrations = [
      'bean_whatever_config_no_type' => [
        'id' => 'bean_whatever_config_no_type',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'embedded_data'],
        'migration_dependencies' => [
          'required' => [
            'dont_touch_this',
            'd7_view_modes',
            'd7_field',
          ],
        ],
        'provider' => 'bean_migrate',
      ],
      'bean:with_type' => [
        'id' => 'bean',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'bean', 'type' => 'with_type'],
        'migration_dependencies' => [
          'optional' => [
            'dont_touch_this',
            'd7_field_instance',
            'd7_field_instance_widget_settings',
            'd7_field_formatter_settings',
          ],
          'required' => [
            'dont_touch_this_one',
            'bean_type:with_type',
            'd7_view_modes',
            'd7_field',
          ],
        ],
        'provider' => 'bean_migrate',
      ],
    ];

    $test_cases = [];
    $test_cases['No core field migrations'] = [
      'Source' => static::UNRELATED_MIGRATION_DEFINITIONS + $bean_migrations,
      'Expected' => static::UNRELATED_MIGRATION_DEFINITIONS + $bean_migrations,
    ];

    $test_cases['No derived core field migrations'] = [
      'Source' => [
        'd7_view_modes' => $d7_view_modes,
        'd7_field' => $d7_field,
        'd7_field_instance' => $d7_field_instance,
        'd7_field_instance_widget_settings' => $d7_field_widget,
        'd7_field_formatter_settings' => $d7_field_formatter,
      ] + $bean_migrations,
      'Expected' => [
        'd7_view_modes' => $d7_view_modes,
        'd7_field' => $d7_field,
        'd7_field_instance' => $d7_field_instance,
        'd7_field_instance_widget_settings' => $d7_field_widget,
        'd7_field_formatter_settings' => $d7_field_formatter,
      ] + $bean_migrations,
    ];

    $test_cases['Some derived field migrations'] = [
      'Source' => [
        'd7_view_modes' => $d7_view_modes,
        'd7_field:notbean' => NestedArray::mergeDeep(
          $d7_field,
          ['source' => ['entity_type' => 'notbean']]
        ),
        'd7_field:bean' => NestedArray::mergeDeep(
          $d7_field,
          ['source' => ['entity_type' => 'bean']]
        ),
        'd7_field_instance' => $d7_field_instance,
        'd7_field_instance_widget_settings:notbean:with_type' => NestedArray::mergeDeep(
          $d7_field_widget,
          ['source' => ['entity_type' => 'notbean', 'bundle' => 'with_type']]
        ),
        'd7_field_instance_widget_settings:bean:with_type' => NestedArray::mergeDeep(
          $d7_field_widget,
          ['source' => ['entity_type' => 'bean', 'bundle' => 'with_type']]
        ),
        'd7_field_formatter_settings:bean:with_type' => NestedArray::mergeDeep(
          $d7_field_formatter,
          ['source' => ['entity_type' => 'bean', 'bundle' => 'with_type']]
        ),
        'd7_field_formatter_settings:bean:not_type' => NestedArray::mergeDeep(
          $d7_field_formatter,
          ['source' => ['entity_type' => 'bean', 'bundle' => 'not_type']]
        ),
      ] + $bean_migrations,
    ];
    $test_cases['Some derived field migrations']['Expected'] = [
      'd7_view_modes' => $test_cases['Some derived field migrations']['Source']['d7_view_modes'],
      'd7_field:notbean' => $test_cases['Some derived field migrations']['Source']['d7_field:notbean'],
      'd7_field:bean' => $test_cases['Some derived field migrations']['Source']['d7_field:bean'],
      'd7_field_instance' => $test_cases['Some derived field migrations']['Source']['d7_field_instance'],
      'd7_field_instance_widget_settings:notbean:with_type' => $test_cases['Some derived field migrations']['Source']['d7_field_instance_widget_settings:notbean:with_type'],
      'd7_field_instance_widget_settings:bean:with_type' => $test_cases['Some derived field migrations']['Source']['d7_field_instance_widget_settings:bean:with_type'],
      'd7_field_formatter_settings:bean:with_type' => $test_cases['Some derived field migrations']['Source']['d7_field_formatter_settings:bean:with_type'],
      'd7_field_formatter_settings:bean:not_type' => $test_cases['Some derived field migrations']['Source']['d7_field_formatter_settings:bean:not_type'],
      'bean_whatever_config_no_type' => [
        'id' => 'bean_whatever_config_no_type',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'embedded_data'],
        'provider' => 'bean_migrate',
        'migration_dependencies' => [
          'required' => [
            'dont_touch_this',
            'd7_view_modes',
            'd7_field:bean',
          ],
        ],
      ],
      'bean:with_type' => [
        'id' => 'bean',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'bean', 'type' => 'with_type'],
        'provider' => 'bean_migrate',
        'migration_dependencies' => [
          'optional' => [
            'dont_touch_this',
            'd7_field_instance',
            'd7_field_instance_widget_settings:bean:with_type',
            'd7_field_formatter_settings:bean:with_type',
          ],
          'required' => [
            'dont_touch_this_one',
            'bean_type:with_type',
            'd7_view_modes',
            'd7_field:bean',
          ],
        ],
      ],
    ];

    $test_cases['All derived field migrations'] = [
      'Source' => [
        'd7_view_modes:bean' => NestedArray::mergeDeep(
          $d7_view_modes,
          ['source' => ['entity_type' => 'bean']]
        ),
        'd7_field_instance:bean:with_type' => NestedArray::mergeDeep(
          $d7_field_instance,
          ['source' => ['entity_type' => 'bean', 'bundle' => 'with_type']]
        ),
        'd7_field:bean' => $test_cases['Some derived field migrations']['Source']['d7_field:bean'],
        'd7_field_instance_widget_settings:bean:with_type' => $test_cases['Some derived field migrations']['Source']['d7_field_instance_widget_settings:bean:with_type'],
        'd7_field_formatter_settings:bean:with_type' => $test_cases['Some derived field migrations']['Source']['d7_field_formatter_settings:bean:with_type'],
      ] + $bean_migrations,
    ];
    $test_cases['All derived field migrations']['Expected'] = [
      'd7_view_modes:bean' => $test_cases['All derived field migrations']['Source']['d7_view_modes:bean'],
      'd7_field_instance:bean:with_type' => $test_cases['All derived field migrations']['Source']['d7_field_instance:bean:with_type'],
      'd7_field:bean' => $test_cases['All derived field migrations']['Source']['d7_field:bean'],
      'd7_field_instance_widget_settings:bean:with_type' => $test_cases['All derived field migrations']['Source']['d7_field_instance_widget_settings:bean:with_type'],
      'd7_field_formatter_settings:bean:with_type' => $test_cases['All derived field migrations']['Source']['d7_field_formatter_settings:bean:with_type'],
      'bean_whatever_config_no_type' => [
        'id' => 'bean_whatever_config_no_type',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'embedded_data'],
        'provider' => 'bean_migrate',
        'migration_dependencies' => [
          'required' => [
            'dont_touch_this',
            'd7_view_modes:bean',
            'd7_field:bean',
          ],
        ],
      ],
      'bean:with_type' => [
        'id' => 'bean',
        'migration_tags' => ['Drupal 7'],
        'source' => ['plugin' => 'bean', 'type' => 'with_type'],
        'provider' => 'bean_migrate',
        'migration_dependencies' => [
          'optional' => [
            'dont_touch_this',
            'd7_field_instance:bean:with_type',
            'd7_field_instance_widget_settings:bean:with_type',
            'd7_field_formatter_settings:bean:with_type',
          ],
          'required' => [
            'dont_touch_this_one',
            'bean_type:with_type',
            'd7_view_modes:bean',
            'd7_field:bean',
          ],
        ],
      ],
    ];

    return $test_cases;
  }

  /**
   * Data provider for ::testAlterFieldInstanceMigrations.
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestAlterFieldInstanceMigrations() {
    $base_d7_fi_definition = [
      'id' => 'd7_field_instance',
      'label' => 'Field instance configuration',
      'source' => [
        'plugin' => 'd7_field_instance',
      ],
      'process' => [
        'entity_type' => 'entity_type',
      ],
      'destination' => [
        'plugin' => 'entity:field_config',
      ],
      'migration_dependencies' => [
        'required' => ['d7_field'],
        'optional' => [
          'd7_node_type',
          'd7_comment_type',
          'd7_taxonomy_vocabulary',
        ],
      ],
    ];

    $test_cases = [
      'A single field instance migration' => [
        'Definitions' => [
          'd7_field_instance' => $base_d7_fi_definition,
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
        'Bean field count' => 1,
        'Expected' => [
          'd7_field_instance' => NestedArray::mergeDeep(
            $base_d7_fi_definition,
            ['migration_dependencies' => ['optional' => ['bean_type']]]
          ),
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
      ],
      'No bean fields are present' => [
        'Definitions' => [
          'd7_field_instance' => $base_d7_fi_definition,
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
        'Bean field count' => 0,
        'Expected' => [
          'd7_field_instance' => $base_d7_fi_definition,
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
      ],
      'No field instance migration' => [
        'Definitions' => static::UNRELATED_MIGRATION_DEFINITIONS,
        'Bean field count' => 0,
        'Expected' => static::UNRELATED_MIGRATION_DEFINITIONS,
      ],
    ];

    $field_instance_node_page = [
      'source' => [
        'plugin' => 'd7_field_instance',
        'entity_type' => 'node',
        'bundle' => 'page',
      ],
    ] + $base_d7_fi_definition;
    $field_instance_taxonomy_term_tags = [
      'source' => [
        'plugin' => 'd7_field_instance',
        'entity_type' => 'taxonomy_term',
        'bundle' => 'tags',
      ],
    ] + $base_d7_fi_definition;
    $field_instance_bean_simple = [
      'source' => [
        'plugin' => 'd7_field_instance',
        'entity_type' => 'bean',
        'bundle' => 'simple',
      ],
    ] + $base_d7_fi_definition;
    $field_instance_bean_complicated = [
      'source' => [
        'plugin' => 'd7_field_instance',
        'entity_type' => 'bean',
        'bundle' => 'complicated',
      ],
    ] + $base_d7_fi_definition;

    $test_cases['Multiple field instance migrations'] = [
      'Definitions' => [
        'd7_field_instance:mapped:type' => $base_d7_fi_definition,
        'd7_field_instance:node:page' => $field_instance_node_page,
        'd7_field_instance:taxonomy_term:tags' => $field_instance_taxonomy_term_tags,
        'd7_field_instance:bean:simple' => $field_instance_bean_simple,
        'd7_field_instance:bean:complicated' => $field_instance_bean_complicated,
      ] + static::UNRELATED_MIGRATION_DEFINITIONS,
      'Bean field count' => 3,
      'Expected' => [
        'd7_field_instance:mapped:type' => NestedArray::mergeDeep(
          $base_d7_fi_definition,
          [
            'migration_dependencies' => [
              'optional' => ['bean_type'],
            ],
          ]
        ),
        'd7_field_instance:node:page' => $field_instance_node_page,
        'd7_field_instance:taxonomy_term:tags' => $field_instance_taxonomy_term_tags,
        'd7_field_instance:bean:simple' => NestedArray::mergeDeep(
          $field_instance_bean_simple,
          [
            'migration_dependencies' => [
              'optional' => ['bean_type'],
            ],
          ]
        ),
        'd7_field_instance:bean:complicated' => NestedArray::mergeDeep(
          $field_instance_bean_complicated,
          [
            'migration_dependencies' => [
              'optional' => ['bean_type'],
            ],
          ]
        ),
      ] + static::UNRELATED_MIGRATION_DEFINITIONS,
    ];

    return $test_cases;
  }

  /**
   * Data provider for ::testFixBlockContentMigrations.
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestFixBlockContentMigrations() {
    return [
      'Block content migration present' => [
        'Definitions' => [
          'd7_custom_block' => [
            'id' => 'd7_block_content',
            'process' => [
              'id' => 'bid',
              'else' => 'remains',
            ],
          ],
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
        'Expected' => [
          'd7_custom_block' => [
            'id' => 'd7_block_content',
            'process' => [
              'else' => 'remains',
            ],
          ],
        ] + static::UNRELATED_MIGRATION_DEFINITIONS,
      ],
      'No Block content migration' => [
        'Definitions' => static::UNRELATED_MIGRATION_DEFINITIONS,
        'Expected' => static::UNRELATED_MIGRATION_DEFINITIONS,
      ],
    ];
  }

  /**
   * Data provider for ::testCopyCoreBlockRegionMappingToBeanBlockPlacement.
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestCopyCoreBlockRegionMappingToBeanBlockPlacement() {
    return [
      'No core migration present' => [
        'Definitions' => static::BEAN_BLOCK_PLACEMENT_DEFINITIONS,
        'Expected' => static::BEAN_BLOCK_PLACEMENT_DEFINITIONS,
      ],
      'Core migration present, but no Bean block placement migrations' => [
        'Definitions' => static::CORE_BLOCK_PLACEMENT_DEFINITION,
        'Expected' => static::CORE_BLOCK_PLACEMENT_DEFINITION,
      ],
      'Core and Bean block placement migrations present' => [
        'Definitions' => static::CORE_BLOCK_PLACEMENT_DEFINITION + static::BEAN_BLOCK_PLACEMENT_DEFINITIONS,
        'Expected' => static::CORE_BLOCK_PLACEMENT_DEFINITION + [
          'bean_block:simple' => [
            'id' => 'bean_block',
            'process' => [
              'region' => [
                [
                  'plugin' => 'plugin_from_core_block_placement',
                  'source' => 'whatever',
                ],
              ],
            ],
          ],
          'bean_block:complicated' => [
            'id' => 'bean_block',
            'process' => [
              'region' => [
                [
                  'plugin' => 'plugin_from_core_block_placement',
                  'source' => 'whatever',
                ],
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * The block placement migration from Drupal core.
   *
   * @const array
   */
  const CORE_BLOCK_PLACEMENT_DEFINITION = [
    'd7_block' => [
      'id' => 'd7_block',
      'process' => [
        'region' => [
          [
            'plugin' => 'plugin_from_core_block_placement',
            'source' => 'whatever',
          ],
        ],
      ],
    ],
  ];

  /**
   * Bean block placement migrations.
   *
   * @const array
   */
  const BEAN_BLOCK_PLACEMENT_DEFINITIONS = [
    'bean_block:simple' => [
      'id' => 'bean_block',
      'process' => [
        'region' => [
          'plugin' => 'whatever',
          'source' => [
            'not touched' => 'yes',
          ],
        ],
      ],
    ],
    'bean_block:complicated' => [
      'id' => 'bean_block',
      'process' => [
        'region' => [
          'plugin' => 'whatever',
          'source' => [
            'not touched' => 'yes',
          ],
        ],
      ],
    ],
  ];

  /**
   * Unrelated migration plugin definitions which shouldn't be touched at all.
   *
   * @const array
   */
  const UNRELATED_MIGRATION_DEFINITIONS = [
    'other_definition' => [
      'id' => 'other_definition',
      'label' => 'Other',
      'source' => [
        'plugin' => 'empty',
      ],
      'process' => [],
      'destination' => [
        'plugin' => 'null',
      ],
      'migration_dependencies' => [
        'required' => [
          'migration_1',
          'migration_2',
        ],
        'optional' => [
          'migration_3',
          'migration_4',
        ],
      ],
    ],
  ];

}
