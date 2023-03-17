<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate;

use Drupal\bean_migrate\Plugin\migrate\BeanDeriver;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\migrate\Plugin\Migration;
use Drupal\user\Plugin\migrate\source\d7\User;

/**
 * Tests the migration deriver class BeanDeriver.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\BeanDeriver
 * @group bean_migrate
 */
class BeanDeriverTest extends BeanDeriverTestBase {

  /**
   * Tests whether bean migrations are derived as expected.
   */
  public function testGetDerivativeDefinitions() {
    // Test whether the "simple" bean migration derivative is generated and it
    // has the "field_body" field value process.
    $migration = $this->getMigration('bean:simple');
    $expected_derivative = static::getExpectedBeanMigrationDefinition('simple', 'Simple');
    $expected_derivative['process']['field_body'] = [
      'plugin' => 'get',
      'source' => 'field_body',
    ];
    $this->assertEquals(
      $expected_derivative,
      static::getImportantMigrationDefinitionProperties($migration)
    );

    // Test whether the "image" bean migration derivative is generated and it
    // has the "field_image" field value process.
    $migration = $this->getMigration('bean:image');
    $expected_derivative = static::getExpectedBeanMigrationDefinition('image', 'Image');
    $expected_derivative['process']['field_image'] = [
      'plugin' => 'sub_process',
      'source' => 'field_image',
      'process' => [
        'target_id' => 'fid',
        'alt' => 'alt',
        'title' => 'title',
        'width' => 'width',
        'height' => 'height',
      ],
    ];
    $this->assertEquals(
      $expected_derivative,
      static::getImportantMigrationDefinitionProperties($migration)
    );

    // Field processes shouldn't be added to the "bean_type" and
    // "bean_title_field_instance" migrations.
    // Test whether the "simple" bean_type migration derivative is generated. It
    // shouldn't have a "field_body" field value process.
    $migration = $this->getMigration('bean_type:simple');
    $this->assertArrayNotHasKey('field_body', $migration->getProcess());
    // Test whether the "simple" bean_title_field_instance migration derivative
    // exists, and whether it is generated without a "field_body" field value
    // process.
    $migration = $this->getMigration('bean_title_field_instance:simple');
    $this->assertArrayNotHasKey('field_body', $migration->getProcess());

    // Test whether the "image" bean_type migration derivative is generated. It
    // shouldn't have a "field_image" field value process.
    $migration = $this->getMigration('bean_type:image');
    $this->assertArrayNotHasKey('field_image', $migration->getProcess());
    // Test whether the "image" bean_title_field_instance migration derivative
    // exists, and whether it is generated without a "field_image" field value
    // process.
    $migration = $this->getMigration('bean_title_field_instance:image');
    $this->assertArrayNotHasKey('field_image', $migration->getProcess());
  }

  /**
   * Tests whether the deriver with an incompatible source plugin fails.
   *
   * It should throw an exception with a meaningful message.
   */
  public function testGetDerivativeDefinitionsException() {
    $deriver = new BeanDeriver('base_definition_id', $this->container->get('migrate_drupal.field_discovery'));

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage(sprintf('"%s" should only used for Bean related migrations. The current migration definition\'s source plugin is a "%s" instance. The definition\s base ID: "%s".', BeanDeriver::class, User::class, 'definition_base_plugin_id'));
    $deriver->getDerivativeDefinitions([
      'id' => 'definition_base_plugin_id',
      'source' => [
        'plugin' => 'd7_user',
      ],
    ]);
  }

  /**
   * Returns the expected Bean content entity migration derivative.
   *
   * @param string $bundle
   *   The source type of the bean entity. With the DB fixture, this
   *   is either "simple" or "image".
   * @param string $bundle_label
   *   The label of the Bean type.
   *
   * @return array
   *   The expected content entity migration derivative's plugin definition.
   */
  protected static function getExpectedBeanMigrationDefinition(string $bundle, string $bundle_label): array {
    return [
      'class' => Migration::class,
      'id' => 'bean',
      'label' => new TranslatableMarkup('@label (@type)', [
        '@label' => 'Bean',
        '@type' => $bundle_label,
      ]),
      'audit' => TRUE,
      'migration_tags' => [
        'Drupal 7',
        'Content',
      ],
      'deriver' => BeanDeriver::class,
      'source' => [
        'plugin' => 'bean',
        'type' => $bundle,
      ],
      'process' => [
        'id' => [
          [
            'plugin' => 'migration_lookup',
            'migration' => "bean:$bundle",
            'no_stub' => TRUE,
            'source' => 'bid',
          ],
          [
            'plugin' => 'default_value',
            'default_value' => [
              NULL,
              NULL,
              NULL,
            ],
          ],
          [
            'plugin' => 'extract',
            'index' => [
              0 => 0,
            ],
          ],
        ],
        'revision_id' => [
          [
            'plugin' => 'migration_lookup',
            'migration' => "bean:$bundle",
            'no_stub' => TRUE,
            'source' => [
              'bid',
              'vid',
            ],
          ],
          [
            'plugin' => 'default_value',
            'default_value' => [
              NULL,
              NULL,
              NULL,
            ],
          ],
          [
            'plugin' => 'extract',
            'index' => [
              0 => 1,
            ],
          ],
        ],
        'uuid' => [
          'plugin' => 'skip_on_empty',
          'method' => 'process',
          'source' => 'uuid',
        ],
        'info' => 'label',
        'type' => [
          [
            'plugin' => 'migration_lookup',
            'migration' => "bean_type:$bundle",
            'no_stub' => TRUE,
            'source' => 'type',
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'row',
          ],
        ],
        'langcode' => [
          'plugin' => 'default_value',
          'source' => 'language',
          'default_value' => 'und',
        ],
        'uid' => 'uid',
        'status' => 'status',
        'changed' => 'changed',
        'revision_user' => 'revision_uid',
        'revision_log' => 'log',
        'revision_created' => 'created',
        'revision_default' => 'revision_default',
        'content_translation_outdated' => [
          'plugin' => 'default_value',
          'source' => 'translate',
          'default_value' => 0,
        ],
        'content_translation_source' => [
          'plugin' => 'default_value',
          'source' => 'source_language',
          'default_value' => 'und',
        ],
        'content_translation_created' => 'etr_created',
        'content_translation_uid' => 'etr_uid',
        'title' => [
          'plugin' => 'null_coalesce',
          'source' => [
            'title_field',
            'title',
          ],
        ],
      ],
      'destination' => [
        'plugin' => 'entity_complete:block_content',
        'translations' => TRUE,
      ],
      'migration_dependencies' => [
        'required' => [
          "bean_type:$bundle",
          "bean_title_field_instance:$bundle",
        ],
        // Although "bean_translation_settings" and "default_language" are
        // optional dependencies (see the migration plugin definition yaml at
        // /migrations/bean.yml), Drupal removes them when they're missing.
        'optional' => [
          "bean_title_field_formatter:$bundle",
          "bean_title_field_widget:$bundle",
          'd7_field_instance_widget_settings',
          'd7_field_formatter_settings',
          'd7_field_instance',
          'd7_user',
          "bean:$bundle",
          "bean_type:$bundle",
        ],
      ],
      '_discovered_file_path' => '/migrations/bean.yml',
      'provider' => 'bean_migrate',
    ];
  }

}
