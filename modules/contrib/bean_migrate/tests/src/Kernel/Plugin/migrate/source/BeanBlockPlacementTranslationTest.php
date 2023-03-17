<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\RequirementsInterface;

/**
 * Tests the "bean_block_placement_translation" migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanBlockPlacementTranslation
 * @group bean_migrate
 */
class BeanBlockPlacementTranslationTest extends BeanSourceTestBase {

  /**
   * {@inheritdoc}
   *
   * @todo This should be changed to "protected" after Drupal core 8.x security
   *   support ends.
   * @see https://www.drupal.org/node/2909426
   */
  public static $modules = [
    'block',
  ];

  /**
   * Tests whether plugin requirements are checked accordingly.
   *
   * @param array $plugin_configuration
   *   The plugin configuration to test with.
   * @param array $database
   *   Database tables and records to test with.
   * @param string|null $expected_message
   *   The expected exception message.
   *
   * @dataProvider providerTestRequirements
   */
  public function testRequirements(array $plugin_configuration, array $database, $expected_message) {
    $plugin = $this->getPlugin($plugin_configuration);
    assert($plugin instanceof RequirementsInterface);

    $this->importSourceDatabase($database);

    if ($expected_message) {
      $this->expectException(RequirementsException::class);
      $this->expectExceptionMessage($expected_message);
    }
    $plugin->checkRequirements();
    $this->assertTrue(!$expected_message);
  }

  /**
   * Data provider for ::testRequirements.
   *
   * @return array
   *   An array of the plugin config, a database structure and values, and the
   *   expected exception message.
   */
  public function providerTestRequirements() {
    $system_table_with_bean = [
      'system' => [
        ['name' => 'bean', 'type' => 'module', 'status' => 1],
      ],
    ];
    $test_cases = [];

    $test_cases['No translation_type config'] = [
      'Plugin config' => [],
      'Db' => [],
      'Expected exception message' => "The 'translation_type' configuration is required and its allowed values are 'i18n', 'entity_translation'",
    ];
    $test_cases['Invalid translation_type config'] = [
      'Plugin config' => [
        'translation_type' => 'missing_type',
      ],
      'Db' => [],
      'Expected exception message' => "The 'translation_type' configuration is required and its allowed values are 'i18n', 'entity_translation'",
    ];

    // I18n block translation.
    $test_cases['I18n translation_type config, no i18n_block'] = [
      'Plugin config' => [
        'translation_type' => 'i18n',
      ],
      'Db' => [
        'system' => [
          ['name' => 'bean', 'type' => 'module', 'status' => 1],
        ],
      ],
      'Expected exception message' => "The 'i18n_block' module isn't enabled on the source site.",
    ];
    $test_cases['I18n translation_type config, requirements met'] = [
      'Plugin config' => [
        'translation_type' => 'i18n',
      ],
      'Db' => [
        'system' => array_merge(
          $system_table_with_bean['system'],
          [['name' => 'i18n_block', 'type' => 'module', 'status' => 1]]
        ),
      ],
      'Expected exception message' => NULL,
    ];

    // Entity translation.
    $test_cases['ET translation_type config, requirements not met'] = [
      'Plugin config' => [
        'translation_type' => 'entity_translation',
      ],
      'Db' => [],
      'Expected exception message' => 'Bean entity title translations cannot be found.',
    ];
    $test_cases['ET translation_type config, requirements met'] = [
      'Plugin config' => [
        'translation_type' => 'entity_translation',
      ],
      'Db' => [
        'system' => array_merge(
          $system_table_with_bean['system'],
          [
            ['name' => 'entity_translation', 'type' => 'module', 'status' => 1],
            ['name' => 'title', 'type' => 'module', 'status' => 1],
          ]
        ),
        'field_data_title_field' => [['data' => 'unconcerned']],
      ],
      'Expected exception message' => NULL,
    ];

    return $test_cases;
  }

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    $test_cases = [];
    $test_cases['I18n, no filtering'] = [
      'Source' => static::BLOCK_PLACEMENT_MULTILINGUAL_TABLES,
      'Expected' => [
        [
          'module' => 'bean',
          'delta' => 'bean-1',
          'theme' => 'bartik',
          'i18n_mode' => '1',
          'title' => 'Title [IS]',
          'status' => '1',
          'type' => 'type_i18n_1',
          'block_id' => '3',
          'langcode' => 'en',
          'translation' => 'Title [EN]',
          'language' => 'en',
          'translation_type' => 'i18n',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
        [
          'module' => 'bean',
          'delta' => 'bean-1',
          'theme' => 'bartik',
          'i18n_mode' => '1',
          'title' => 'Title [IS]',
          'status' => '1',
          'type' => 'type_i18n_1',
          'block_id' => '3',
          'langcode' => 'hu',
          'translation' => 'Title [HU]',
          'language' => 'hu',
          'translation_type' => 'i18n',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
        [
          'module' => 'bean',
          'delta' => 'bean-4',
          'theme' => 'bartik',
          'i18n_mode' => '1',
          'title' => 'Title [IS]',
          'status' => '1',
          'type' => 'type_i18n_2',
          'block_id' => '6',
          'langcode' => 'en',
          'translation' => 'Title [EN]',
          'language' => 'en',
          'translation_type' => 'i18n',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'translation_type' => 'i18n',
      ],
    ];
    $test_cases['I18n, with filtering'] = [
      'Source' => static::BLOCK_PLACEMENT_MULTILINGUAL_TABLES,
      'Expected' => [
        $test_cases['I18n, no filtering']['Expected'][2],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'translation_type' => 'i18n',
        'type' => 'type_i18n_2',
      ],
    ];

    $test_cases['Entity translation, no filtering'] = [
      'Source' => static::BLOCK_PLACEMENT_MULTILINGUAL_TABLES,
      'Expected' => [
        [
          'module' => 'bean',
          'delta' => 'bean-2',
          'theme' => 'seven',
          'i18n_mode' => '1',
          'title' => '',
          'status' => '1',
          'type' => 'type_et_1',
          'block_id' => '4',
          'langcode' => 'en',
          'language' => 'en',
          'translation' => 'Bean #2 translatable title [EN]',
          'translation_type' => 'entity_translation',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
        [
          'module' => 'bean',
          'delta' => 'bean-2',
          'theme' => 'seven',
          'i18n_mode' => '1',
          'title' => '',
          'status' => '1',
          'type' => 'type_et_1',
          'block_id' => '4',
          'langcode' => 'hu',
          'language' => 'hu',
          'translation' => 'Bean #2 translatable title [HU]',
          'translation_type' => 'entity_translation',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
        [
          'module' => 'bean',
          'delta' => 'bean-3',
          'theme' => 'bartik',
          'i18n_mode' => '0',
          'title' => '',
          'status' => '1',
          'type' => 'type_et_2',
          'block_id' => '5',
          'langcode' => 'hu',
          'language' => 'hu',
          'translation' => 'Bean #3 title [HU]',
          'translation_type' => 'entity_translation',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'translation_type' => 'entity_translation',
      ],
    ];
    $test_cases['Entity translation, with filtering'] = [
      'Source' => $test_cases['Entity translation, no filtering']['Source'],
      'Expected' => [
        $test_cases['Entity translation, no filtering']['Expected'][0],
        $test_cases['Entity translation, no filtering']['Expected'][1],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'translation_type' => 'entity_translation',
        'type' => 'type_et_1',
      ],
    ];

    return $test_cases;
  }

  /**
   * Database tables and records used in the test.
   *
   * @const array[][]
   */
  const BLOCK_PLACEMENT_MULTILINGUAL_TABLES = [
    'system' => [
      [
        'name' => 'system',
        'schema_version' => 7001,
        'type' => 'module',
        'status' => 1,
      ],
      [
        'name' => 'entity_translation',
        'schema_version' => 7001,
        'type' => 'module',
        'status' => 1,
      ],
      [
        'name' => 'title',
        'schema_version' => 7001,
        'type' => 'module',
        'status' => 1,
      ],
      [
        'name' => 'i18n_block',
        'schema_version' => 7001,
        'type' => 'module',
        'status' => 1,
      ],
    ],
    'languages' => [
      [
        'language' => 'en',
        'enabled' => 1,
      ],
      [
        'language' => 'hu',
        'enabled' => 1,
      ],
      [
        'language' => 'is',
        'enabled' => 1,
      ],
    ],
    'variable' => [
      [
        'name' => 'language_default',
        'value' => 'O:8:"stdClass":1:{s:8:"language";s:2:"is";}',
      ],
    ],
    'block_role' => [
      [
        'module' => '',
        'delta' => '',
        'rid' => 2,
      ],
    ],
    'role' => [
      ['rid' => 1],
    ],
    // Block tables.
    'block' => [
      [
        'bid' => 1,
        'module' => 'system',
        'delta' => 'main',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => 0,
        'region' => 'content',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => '',
        'cache' => -1,
        'i18n_mode' => 0,
      ],
      [
        'bid' => 2,
        'module' => 'user',
        'delta' => 'login',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => 0,
        'region' => 'sidebar_first',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => '',
        'cache' => -1,
        'i18n_mode' => 1,
      ],
      [
        'bid' => 3,
        'module' => 'bean',
        'delta' => 'bean-1',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => 0,
        'region' => 'featured',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => 'Title [IS]',
        'cache' => -1,
        'i18n_mode' => 1,
      ],
      [
        'bid' => 4,
        'module' => 'bean',
        'delta' => 'bean-2',
        'theme' => 'seven',
        'status' => 1,
        'weight' => -9,
        'region' => 'content',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => '',
        'cache' => -1,
        'i18n_mode' => 1,
      ],
      [
        'bid' => 5,
        'module' => 'bean',
        'delta' => 'bean-3',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => -9,
        'region' => 'footer_first',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => '',
        'cache' => -1,
        'i18n_mode' => 0,
      ],
      [
        'bid' => 6,
        'module' => 'bean',
        'delta' => 'bean-4',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => 0,
        'region' => 'footer_last',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => 'Title [IS]',
        'cache' => -1,
        'i18n_mode' => 1,
      ],
    ],
    // Bean table.
    'bean' => [
      [
        'bid' => 1,
        'vid' => 1,
        'delta' => 'bean-1',
        'title' => 'Bean #1 title',
        'type' => 'type_i18n_1',
      ],
      [
        'bid' => 2,
        'vid' => 3,
        'delta' => 'bean-2',
        'title' => 'Bean #2 title',
        'type' => 'type_et_1',
      ],
      [
        'bid' => 3,
        'vid' => 8,
        'delta' => 'bean-3',
        'title' => 'Bean #3 title [IS]',
        'type' => 'type_et_2',
      ],
      [
        'bid' => 4,
        'vid' => 13,
        'delta' => 'bean-4',
        'title' => 'Bean #4 title',
        'type' => 'type_i18n_2',
      ],
    ],
    'i18n_string' => [
      [
        'lid' => 1,
        'textgroup' => 'blocks',
        'context' => 'bean:bean-1:title',
        'objectid' => 'bean-1',
        'type' => 'bean',
        'property' => 'title',
        'objectindex' => 0,
        'format' => NULL,
      ],
      [
        'lid' => 33,
        'textgroup' => 'blocks',
        'context' => 'bean:bean-4:title',
        'objectid' => 'bean-4',
        'type' => 'bean',
        'property' => 'title',
        'objectindex' => 0,
        'format' => NULL,
      ],
    ],
    'locales_target' => [
      [
        'lid' => 1,
        'translation' => 'Title [EN]',
        'language' => 'en',
        'plid' => 0,
        'plural' => 0,
        'i18n_status' => 0,
      ],
      [
        'lid' => 1,
        'translation' => 'Title [HU]',
        'language' => 'hu',
        'plid' => 0,
        'plural' => 0,
        'i18n_status' => 0,
      ],
      [
        'lid' => 33,
        'translation' => 'Title [EN]',
        'language' => 'en',
        'plid' => 0,
        'plural' => 0,
        'i18n_status' => 0,
      ],
    ],
    'entity_translation' => [
      [
        'entity_type' => 'bean',
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'hu',
        'source' => '',
        'status' => 1,
      ],
      [
        'entity_type' => 'bean',
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'is',
        'source' => 'hu',
        'status' => 1,
      ],
      [
        'entity_type' => 'bean',
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'en',
        'source' => 'is',
        'status' => 1,
      ],
      [
        'entity_type' => 'bean',
        'entity_id' => 3,
        'revision_id' => 8,
        'language' => 'is',
        'source' => '',
        'status' => 1,
      ],
      [
        'entity_type' => 'bean',
        'entity_id' => 3,
        'revision_id' => 8,
        'language' => 'hu',
        'source' => 'is',
        'status' => 1,
      ],
      [
        'entity_type' => 'bean',
        'entity_id' => 3,
        'revision_id' => 8,
        'language' => 'en',
        'source' => 'is',
        'status' => 1,
      ],
    ],
    'field_data_title_field' => [
      [
        'entity_type' => 'bean',
        'bundle' => 'type_et_1',
        'deleted' => 0,
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'hu',
        'delta' => 0,
        'title_field_value' => 'Bean #2 translatable title [HU]',
      ],
      [
        'entity_type' => 'bean',
        'bundle' => 'type_et_1',
        'deleted' => 0,
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'is',
        'delta' => 0,
        'title_field_value' => 'Bean #2 translatable title [IS]',
      ],
      [
        'entity_type' => 'bean',
        'bundle' => 'type_et_1',
        'deleted' => 0,
        'entity_id' => 2,
        'revision_id' => 4,
        'language' => 'en',
        'delta' => 0,
        'title_field_value' => 'Bean #2 translatable title [EN]',
      ],


      [
        'entity_type' => 'bean',
        'bundle' => 'type_et_2',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 8,
        'language' => 'is',
        'delta' => 0,
        'title_field_value' => 'Bean #3 title [IS]',
      ],
      [
        'entity_type' => 'bean',
        'bundle' => 'type_et_2',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 8,
        'language' => 'hu',
        'delta' => 0,
        'title_field_value' => 'Bean #3 title [HU]',
      ],
    ],
  ];

}
