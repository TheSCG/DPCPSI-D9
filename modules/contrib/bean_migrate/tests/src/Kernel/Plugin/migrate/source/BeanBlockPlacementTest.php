<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

/**
 * Tests the bean block placement migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanBlockPlacement
 * @group bean_migrate
 */
class BeanBlockPlacementTest extends BeanSourceTestBase {

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
   * {@inheritdoc}
   */
  public function providerSource() {
    $test_cases = [
      'No bean block placements' => [
        'Source' => static::SYSTEM_TABLE + static::BLOCK_TABLE_PLAIN + static::BEAN_TABLE,
        'Expected' => [],
      ],
      'Bean block placements, no filtering' => [
        'Source' => static::SYSTEM_TABLE + static::BLOCK_TABLE_BEAN + static::BEAN_TABLE + static::BLOCK_ROLE_TABLE,
        'Expected' => [
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
            'title' => '<none>',
            'cache' => -1,
            'bean_id' => 1,
            'bean_revision_id' => 1,
            'bean_title' => 'Bean #1 title',
            'type' => 'simple',
            'default_theme' => 'Garland',
            'admin_theme' => NULL,
            'roles' => [],
            'settings' => [],
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
            'title' => 'Bean #2 block title override',
            'cache' => -1,
            'bean_id' => 2,
            'bean_revision_id' => 3,
            'bean_title' => 'Bean #2 title',
            'type' => 'complicated',
            'default_theme' => 'Garland',
            'admin_theme' => NULL,
            'roles' => [],
            'settings' => [],
          ],
          [
            'bid' => 5,
            'module' => 'bean',
            'delta' => 'bean-translated',
            'theme' => 'bartik',
            'status' => 1,
            'weight' => 0,
            'region' => 'footer_first',
            'custom' => 0,
            'visibility' => 0,
            'pages' => '',
            'title' => '',
            'cache' => -1,
            'bean_id' => 3,
            'bean_revision_id' => 5,
            'bean_title' => 'Bean #3 title [EN, default]',
            'type' => 'bean_translated',
            'default_theme' => 'Garland',
            'admin_theme' => NULL,
            'roles' => [],
            'settings' => [],
          ],
        ],
      ],
    ];

    $test_cases['Bean block placements, filtering to a missing type'] = [
      'Source' => $test_cases['Bean block placements, no filtering']['Source'],
      'Expected' => [],
      'Count' => NULL,
      'Config' => [
        'type' => 'missing_type',
      ],
    ];

    $test_cases['Bean block placements, filtering to "simple" type'] = [
      'Source' => $test_cases['Bean block placements, no filtering']['Source'],
      'Expected' => [
        $test_cases['Bean block placements, no filtering']['Expected'][0],
      ],
      'Count' => NULL,
      'Config' => [
        'type' => 'simple',
      ],
    ];

    $test_cases['With translated bean'] = [
      'Source' => static::SYSTEM_TABLE_WITH_TRANSLATIONS + static::BLOCK_TABLE_BEAN + static::BEAN_TABLE + static::BLOCK_ROLE_TABLE + static::TITLE_FIELD_VALUE_TABLE + static::VARIABLE_TABLE,
      'Expected' => [
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
          'title' => '<none>',
          'cache' => -1,
          'bean_id' => 1,
          'bean_revision_id' => 1,
          'bean_title' => 'Bean #1 title',
          'type' => 'simple',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
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
          'title' => 'Bean #2 block title override',
          'cache' => -1,
          'bean_id' => 2,
          'bean_revision_id' => 3,
          'bean_title' => 'Bean #2 title',
          'type' => 'complicated',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
        [
          'bid' => 5,
          'module' => 'bean',
          'delta' => 'bean-translated',
          'theme' => 'bartik',
          'status' => 1,
          'weight' => 0,
          'region' => 'footer_first',
          'custom' => 0,
          'visibility' => 0,
          'pages' => '',
          'title' => '',
          'cache' => -1,
          'bean_id' => 3,
          'bean_revision_id' => 5,
          // The bean entity's default language is English, but the site's
          // default language is Icelandic.
          'bean_title' => 'Bean #3 title [EN, default]',
          'bean_title_default' => 'Bean #3 title [IS]',
          'type' => 'bean_translated',
          'default_theme' => 'Garland',
          'admin_theme' => NULL,
          'roles' => [],
          'settings' => [],
        ],
      ],
    ];

    return $test_cases;
  }

  /**
   * Bean table and records used in the test.
   *
   * @const array[][]
   */
  const BEAN_TABLE = [
    'bean' => [
      [
        'bid' => 1,
        'vid' => 1,
        'delta' => 'bean-1',
        'title' => 'Bean #1 title',
        'type' => 'simple',
      ],
      [
        'bid' => 2,
        'vid' => 3,
        'delta' => 'bean-2',
        'title' => 'Bean #2 title',
        'type' => 'complicated',
      ],
      [
        'bid' => 3,
        'vid' => 5,
        'delta' => 'bean-translated',
        'title' => 'Bean #3 title [EN, default]',
        'type' => 'bean_translated',
      ],
    ],
  ];

  /**
   * Block table and records used in the test, without bean blocks.
   *
   * @const array[][]
   */
  const BLOCK_TABLE_PLAIN = [
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
      ],
    ],
  ];

  /**
   * Block table and records used in the test, without bean blocks.
   *
   * @const array[][]
   */
  const BLOCK_TABLE_BEAN = [
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
        'title' => '<none>',
        'cache' => -1,
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
        'title' => 'Bean #2 block title override',
        'cache' => -1,
      ],
      [
        'bid' => 5,
        'module' => 'bean',
        'delta' => 'bean-translated',
        'theme' => 'bartik',
        'status' => 1,
        'weight' => 0,
        'region' => 'footer_first',
        'custom' => 0,
        'visibility' => 0,
        'pages' => '',
        'title' => '',
        'cache' => -1,
      ],
    ],
  ];

  /**
   * System table. Used for determining the source Drupal version.
   *
   * This is used by the parent source plugin for determining what is the name
   * of the table which stores block placements.
   *
   * @const array[][]
   */
  const SYSTEM_TABLE = [
    'system' => [
      [
        'name' => 'system',
        'schema_version' => 7001,
        'type' => 'module',
        'status' => 1,
      ],
    ],
  ];

  /**
   * System table with bean entity translations.
   *
   * @const array[][]
   */
  const SYSTEM_TABLE_WITH_TRANSLATIONS = [
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
    ],
  ];

  /**
   * Emtpy block role table and records used in the test.
   *
   * @const array[][]
   */
  const BLOCK_ROLE_TABLE = [
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
  ];

  /**
   * Title field value table.
   *
   * @const array[][]
   */
  const TITLE_FIELD_VALUE_TABLE = [
    'field_data_title_field' => [
      [
        'entity_type' => 'custom_et',
        'bundle' => 'bean_translated',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 5,
        'language' => 'is',
        'delta' => 0,
        'title_field_value' => 'Custom entity #3 [IS]',
      ],
      [
        'entity_type' => 'custom_et',
        'bundle' => 'bean_translated',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 5,
        'language' => 'en',
        'delta' => 0,
        'title_field_value' => 'Custom entity #3 [EN]',
      ],
      [
        'entity_type' => 'bean',
        'bundle' => 'bean_translated',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 5,
        'language' => 'is',
        'delta' => 0,
        'title_field_value' => 'Bean #3 title [IS]',
      ],
      [
        'entity_type' => 'bean',
        'bundle' => 'bean_translated',
        'deleted' => 0,
        'entity_id' => 3,
        'revision_id' => 5,
        'language' => 'en',
        'delta' => 0,
        'title_field_value' => 'Bean #3 title [EN, default]',
      ],
    ],
  ];

  /**
   * Variable table.
   *
   * @const array[][]
   */
  const VARIABLE_TABLE = [
    'variable' => [
      [
        'name' => 'language_default',
        'value' => 'O:8:"stdClass":1:{s:8:"language";s:2:"is";}',
      ],
    ],
  ];

}
