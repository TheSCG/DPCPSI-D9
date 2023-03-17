<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate;

use Drupal\bean_migrate\Plugin\migrate\BeanBlockDeriver;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\migrate\Plugin\Migration;
use Drupal\system\Plugin\migrate\source\Menu;

/**
 * Tests the migration deriver class BeanBlockDeriver.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\BeanBlockDeriver
 * @group bean_migrate
 */
class BeanBlockDeriverTest extends BeanDeriverTestBase {

  /**
   * Tests whether "bean_block" migrations are derived as expected.
   */
  public function testGetDerivativeDefinitions() {
    // Test whether the "simple" bean_block migration derivative is generated
    // and checks its definition.
    $migration = $this->getMigration('bean_block:simple');
    $this->assertEquals(
      static::getExpectedBeanBlockMigrationDefinition('simple'),
      static::getImportantMigrationDefinitionProperties($migration)
    );

    // Check the "simple" bean_block migration derivative.
    $migration = $this->getMigration('bean_block:image');
    $this->assertEquals(
      static::getExpectedBeanBlockMigrationDefinition('image'),
      static::getImportantMigrationDefinitionProperties($migration)
    );

    // Check an i18n block  "simple" bean_block migration derivative.
    $migration = $this->getMigration('bean_block:simple');
    $this->assertEquals(
      static::getExpectedBeanBlockMigrationDefinition('simple'),
      static::getImportantMigrationDefinitionProperties($migration)
    );
  }

  /**
   * Tests whether the deriver with an incompatible source plugin fails.
   *
   * It should throw an exception with a meaningful message.
   */
  public function testGetDerivativeDefinitionsException() {
    $deriver = new BeanBlockDeriver();

    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage(sprintf("'%s' should only used for Bean block placement migrations. The current migration definition's source plugin is a '%s' instance. The definition's base ID is '%s'.", BeanBlockDeriver::class, Menu::class, 'definition_base_plugin_id'));
    $deriver->getDerivativeDefinitions([
      'id' => 'definition_base_plugin_id',
      'source' => [
        'plugin' => 'menu',
      ],
    ]);
  }

  /**
   * Returns the expected Bean block placement migration derivative.
   *
   * @param string $bundle
   *   The source type of the related bean blocks. With the DB fixture, this
   *   is either "simple" or "image".
   *
   * @return array
   *   The expected Bean block placement migration derivative's plugin
   *   definition.
   */
  protected static function getExpectedBeanBlockMigrationDefinition(string $bundle): array {
    return [
      'class' => Migration::class,
      'id' => 'bean_block',
      'label' => new TranslatableMarkup('@label (@type)', [
        '@label' => 'Bean block placement',
        '@type' => $bundle,
      ]),
      'migration_tags' => [
        'Drupal 7',
        'Configuration',
      ],
      'deriver' => BeanBlockDeriver::class,
      'source' => [
        'plugin' => 'bean_block_placement',
        'constants' => [
          'status' => 1,
        ],
        'type' => $bundle,
      ],
      'process' => [
        'status' => 'constants/status',
        'theme' => [
          [
            'plugin' => 'block_theme',
            'source' => [
              'theme',
              'default_theme',
              'admin_theme',
            ],
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'row',
          ],
        ],
        'id' => [
          [
            'plugin' => 'concat',
            'source' => [
              '@theme',
              'module',
              'delta',
            ],
            'delimiter' => '_',
          ],
          [
            'plugin' => 'machine_name',
            'field' => 'id',
          ],
        ],
        'plugin' => [
          [
            'plugin' => 'bean_block_plugin_id',
            'source' => [
              'bean_id',
              'bean_revision_id',
            ],
          ],
          [
            'plugin' => 'skip_on_empty',
            'method' => 'row',
          ],
        ],
        'region' => [
          'plugin' => 'block_region',
          'source' => [
            'theme',
            '@theme',
            'region',
          ],
          'map' => [
            'bartik' => [
              'bartik' => [
                'featured' => 'featured_top',
                'triptych_first' => 'featured_bottom_first',
                'triptych_middle' => 'featured_bottom_second',
                'triptych_last' => 'featured_bottom_third',
                'footer_firstcolumn' => 'footer_first',
                'footer_secondcolumn' => 'footer_second',
                'footer_thirdcolumn' => 'footer_third',
                'footer_fourthcolumn' => 'footer_fourth',
                'footer' => 'footer_fifth',
              ],
            ],
          ],
          'default_value' => 'content',
        ],
        'weight' => 'weight',
        'title_or_null' => [
          'plugin' => 'default_value',
          'source' => 'title',
          'default_value' => NULL,
        ],
        'label' => [
          'plugin' => 'null_coalesce',
          'source' => [
            '@title_or_null',
            'bean_title_default',
            'bean_title',
          ],
        ],
        'settings' => [
          'plugin' => 'block_settings',
          'source' => [
            '@plugin',
            'delta',
            'settings',
            '@label',
          ],
        ],
        'visibility' => [
          'plugin' => 'block_visibility',
          'source' => [
            'visibility',
            'pages',
            'roles',
          ],
          'skip_php' => TRUE,
        ],
      ],
      'destination' => [
        'plugin' => 'entity_bean_block',
      ],
      'migration_dependencies' => [
        'required' => [
          "bean:$bundle",
        ],
        // Although "default_language" is listed as optional dependency (check
        // the  migration plugin definition yaml at /migrations/bean_block.yml),
        // Drupal removes it when the dependecy requirements are not met.
        'optional' => [
          'd7_user_role',
        ],
      ],
      '_discovered_file_path' => '/migrations/bean_block.yml',
      'provider' => 'bean_migrate',
    ];
  }

}
