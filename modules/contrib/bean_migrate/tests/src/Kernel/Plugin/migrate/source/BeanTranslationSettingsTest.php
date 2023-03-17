<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

use Drupal\migrate\Exception\RequirementsException;

/**
 * Tests the "bean_translation_settings" migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanTranslationSettings
 * @group bean_migrate
 */
class BeanTranslationSettingsTest extends BeanSourceTestBase {

  /**
   * {@inheritdoc}
   *
   * @dataProvider providerSource
   */
  public function testSource(array $source_data, array $expected_data, $expected_count = NULL, array $configuration = [], $high_water = NULL, $expected_cache_key = NULL, bool $content_translation_enabled = FALSE) {
    if ($content_translation_enabled) {
      $this->enableModules(['content_translation']);
    }

    parent::testSource($source_data, $expected_data, $expected_count, $configuration, $high_water, $expected_cache_key);
  }

  /**
   * Tests whether the expected exception is thrown when requirements not met.
   *
   * @param array $database
   *   The source data that the source plugin will read.
   * @param string|null $exception_class
   *   The expected exception class, if an exception should be thrown.
   * @param string|null $exception_message
   *   The expected exception message, if an exception should be thrown.
   *
   * @dataProvider providerTestCheckRequirements
   *
   * @throws \ReflectionException
   */
  public function testCheckRequirements(array $database, string $exception_class = NULL, string $exception_message = NULL) {
    $plugin = $this->getPlugin([]);
    $reflector = new \ReflectionObject($plugin);
    $property = $reflector->getProperty('database');
    $property->setAccessible(TRUE);
    $property->setValue($plugin, $this->getDatabase($database));

    // Assert that when bean and entity_translation are enabled, no exception is
    // thrown.
    if (empty($exception_class)) {
      try {
        $plugin->checkRequirements();
        $this->pass('No exception was thrown when all requirements are met.');
      }
      catch (\Exception $e) {
        $this->fail(sprintf("A(n) '%s' exception was thrown when all requirements are met.", get_class($e)));
      }
      return;
    }

    $this->expectException($exception_class);
    $this->expectExceptionMessage($exception_message);
    $plugin->checkRequirements();

  }

  /**
   * Data provider for ::testCheckRequirements().
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestCheckRequirements(): array {
    $db = static::SYSTEM_TABLE;
    $test_cases = [];
    $test_cases['No exception'] = [
      'Database' => $db,
      'Exception class' => NULL,
      'Exception message' => NULL,
    ];

    $db['system']['entity_translation']['status'] = 0;
    $test_cases['No entity_translation module'] = [
      'Database' => $db,
      'Exception class' => RequirementsException::class,
      'Exception message' => 'The Entity Translation module is not enabled in the source site.',
    ];

    $db['system']['bean']['status'] = 0;
    $test_cases['No entity_translation, no bean module'] = [
      'Database' => $db,
      'Exception class' => RequirementsException::class,
      'Exception message' => 'The module bean is not enabled in the source site.',
    ];

    return $test_cases;
  }

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    $test_cases = [
      'All bundles' => [
        'Source' => static::SYSTEM_TABLE + static::DB_TABLES,
        'Expected' => [
          [
            'type' => 'fully_translatable',
            'content_translation_enabled' => 1,
            'language_alterable' => 1,
            'default_langcode' => 'xx-et-default',
            'untranslatable_fields_hide' => 0,
            'langcode_include_locked' => 0,
            'langcode_weight' => 5,
            'et_settings_serialized' => 'a:5:{s:16:"default_language";s:13:"xx-et-default";s:22:"hide_language_selector";i:0;s:21:"exclude_language_none";i:1;s:13:"lock_language";i:1;s:27:"shared_fields_original_only";i:0;}',
            'fb_settings_serialized' => 'a:0:{}',
            'cache_key' => 'e87263b21c215c2eb10ce17e2f8d346815a8734799f955adf127d769fb0e3401',
          ],
          [
            'type' => 'no_translations',
            'content_translation_enabled' => 0,
            'language_alterable' => 0,
            'default_langcode' => 'und',
            'untranslatable_fields_hide' => 0,
            'langcode_include_locked' => 1,
            'langcode_weight' => 5,
            'et_settings_serialized' => 'a:5:{s:16:"default_language";s:3:"und";s:22:"hide_language_selector";i:1;s:21:"exclude_language_none";i:0;s:13:"lock_language";i:0;s:27:"shared_fields_original_only";i:0;}',
            'fb_settings_serialized' => 'a:0:{}',
            'cache_key' => 'e87263b21c215c2eb10ce17e2f8d346815a8734799f955adf127d769fb0e3401',
          ],
          [
            'type' => 'partially_translatable',
            'content_translation_enabled' => 1,
            'language_alterable' => 1,
            'default_langcode' => 'und',
            'untranslatable_fields_hide' => 0,
            'langcode_include_locked' => 1,
            'langcode_weight' => 19,
            'et_settings_serialized' => 'a:5:{s:16:"default_language";s:3:"und";s:22:"hide_language_selector";i:0;s:21:"exclude_language_none";i:0;s:13:"lock_language";i:1;s:27:"shared_fields_original_only";i:0;}',
            'fb_settings_serialized' => 'a:1:{s:12:"extra_fields";a:1:{s:4:"form";a:1:{s:8:"language";a:1:{s:6:"weight";s:2:"19";}}}}',
            'cache_key' => 'e87263b21c215c2eb10ce17e2f8d346815a8734799f955adf127d769fb0e3401',
          ],
        ],
        'Count' => NULL,
        'Plugin config' => [],
        'High water value' => NULL,
        'Cache key' => NULL,
        'Content Translation enabled' => TRUE,
      ],
    ];

    $test_cases['Filtering for "partially_translatable"'] = [
      'Source' => $test_cases['All bundles']['Source'],
      'Expected' => [
        [
          'type' => 'partially_translatable',
          'content_translation_enabled' => 1,
          'language_alterable' => 1,
          'default_langcode' => 'und',
          'untranslatable_fields_hide' => 0,
          'langcode_include_locked' => 1,
          'langcode_weight' => 19,
          'et_settings_serialized' => 'a:5:{s:16:"default_language";s:3:"und";s:22:"hide_language_selector";i:0;s:21:"exclude_language_none";i:0;s:13:"lock_language";i:1;s:27:"shared_fields_original_only";i:0;}',
          'fb_settings_serialized' => 'a:1:{s:12:"extra_fields";a:1:{s:4:"form";a:1:{s:8:"language";a:1:{s:6:"weight";s:2:"19";}}}}',
          'cache_key' => '89c2ac36264e24d9ca2d7eac23d259a484acd5a83fdd055b1abdb93ec518e72c',
        ],
      ],
      'Count' => NULL,
      'Plugin config' => [
        'type' => 'partially_translatable',
      ],
      'High water value' => NULL,
      'Cache key' => NULL,
      'Content Translation enabled' => TRUE,
    ];
    $test_cases['Filtering for a missing type'] = [
      'Source' => $test_cases['All bundles']['Source'],
      'Expected' => [],
      'Count' => NULL,
      'Plugin config' => [
        'type' => 'missing_type',
      ],
      'High water value' => NULL,
      'Cache key' => NULL,
      'Content Translation enabled' => TRUE,
    ];

    foreach ($test_cases as $case_description => $case) {
      $case['Expected'] = array_reduce($case['Expected'], function (array $carry, array $expected_rows) {
        $carry[] = array_filter($expected_rows, function (string $key) {
          return !in_array($key, [
            'content_translation_enabled',
            'untranslatable_fields_hide',
          ], TRUE);
        }, ARRAY_FILTER_USE_KEY);
        return $carry;
      }, []);
      $case['Content Translation enabled'] = FALSE;

      $test_cases[$case_description . ', without content_translation'] = $case;
    }

    return $test_cases;
  }

  /**
   * System table. Used for determining whether requirements are met.
   *
   * @const array[][]
   */
  const SYSTEM_TABLE = [
    'system' => [
      'system' => [
        'name' => 'system',
        'schema_version' => 7084,
        'type' => 'module',
        'status' => 1,
      ],
      'entity_translation' => [
        'name' => 'entity_translation',
        'schema_version' => 7009,
        'type' => 'module',
        'status' => 1,
      ],
      'bean' => [
        'name' => 'bean',
        'schema_version' => 7013,
        'type' => 'module',
        'status' => 1,
      ],
    ],
  ];

  /**
   * Database tables and records used in the source test.
   *
   * Contains bean, field_config, field_config_instance and variable records.
   *
   * @const array[][]
   */
  const DB_TABLES = [
    'bean' => [
      ['type' => 'no_translations'],
      ['type' => 'partially_translatable'],
      ['type' => 'fully_translatable'],
      ['type' => 'fully_translatable'],
      ['type' => 'no_translations'],
      ['type' => 'partially_translatable'],
      ['type' => 'partially_translatable'],
    ],
    'field_config' => [
      [
        'id' => 1,
        'translatable' => 0,
        'active' => 1,
        'storage_active' => 1,
        'deleted' => 0,
      ],
      [
        'id' => 2,
        'translatable' => 0,
        'active' => 1,
        'storage_active' => 1,
        'deleted' => 0,
      ],
      [
        'id' => 3,
        'translatable' => 1,
        'active' => 1,
        'storage_active' => 1,
        'deleted' => 0,
      ],
      [
        'id' => 4,
        'translatable' => 1,
        'active' => 1,
        'storage_active' => 1,
        'deleted' => 0,
      ],
    ],
    'field_config_instance' => [
      [
        'field_id' => 1,
        'entity_type' => 'bean',
        'bundle' => 'no_translations',
        'deleted' => 0,
      ],
      [
        'field_id' => 2,
        'entity_type' => 'bean',
        'bundle' => 'partially_translatable',
        'deleted' => 0,
      ],
      [
        'field_id' => 3,
        'entity_type' => 'bean',
        'bundle' => 'partially_translatable',
        'deleted' => 0,
      ],
      [
        'field_id' => 3,
        'entity_type' => 'bean',
        'bundle' => 'fully_translatable',
        'deleted' => 0,
      ],
      [
        'field_id' => 4,
        'entity_type' => 'node',
        'bundle' => 'no_translations',
        'deleted' => 0,
      ],
    ],
    'variable' => [
      [
        'name' => 'entity_translation_settings_bean__no_translations',
        'value' => 'a:5:{s:16:"default_language";s:3:"und";s:22:"hide_language_selector";i:1;s:21:"exclude_language_none";i:0;s:13:"lock_language";i:0;s:27:"shared_fields_original_only";i:0;}',
      ],
      [
        'name' => 'entity_translation_settings_bean__partially_translatable',
        'value' => 'a:5:{s:16:"default_language";s:3:"und";s:22:"hide_language_selector";i:0;s:21:"exclude_language_none";i:0;s:13:"lock_language";i:1;s:27:"shared_fields_original_only";i:0;}',
      ],
      [
        'name' => 'entity_translation_settings_bean__fully_translatable',
        'value' => 'a:5:{s:16:"default_language";s:13:"xx-et-default";s:22:"hide_language_selector";i:0;s:21:"exclude_language_none";i:1;s:13:"lock_language";i:1;s:27:"shared_fields_original_only";i:0;}',
      ],
      [
        'name' => 'field_bundle_settings_bean__no_translations',
        'value' => 'a:0:{}',
      ],
      [
        'name' => 'field_bundle_settings_bean__partially_translatable',
        'value' => 'a:1:{s:12:"extra_fields";a:1:{s:4:"form";a:1:{s:8:"language";a:1:{s:6:"weight";s:2:"19";}}}}',
      ],
      [
        'name' => 'field_bundle_settings_bean__fully_translatable',
        'value' => 'a:0:{}',
      ],
    ],
  ];

}
