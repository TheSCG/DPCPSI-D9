<?php

namespace Drupal\Tests\bean_migrate\Unit\Plugin\migrate\process;

use Drupal\bean_migrate\Plugin\migrate\process\BeanUniqueType;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;
use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;
use Prophecy\Argument;

/**
 * Tests the "bean_unique_type" migrate process plugin.
 *
 * @coversDefaultClass \Drupal\bean_migrate\Plugin\migrate\process\BeanUniqueType
 * @group bean_migrate
 */
class BeanUniqueTypeTest extends MigrateProcessTestCase {

  /**
   * Tests that transform work as expected.
   *
   * @dataProvider providerTestTransform
   */
  public function testTransform(string $source, array $preexisting_types, bool $core_type_migration_exists, string $expected) {
    $preexisting_types = !empty($preexisting_types)
      ? array_combine($preexisting_types, $preexisting_types)
      : $preexisting_types;

    $block_content_type_storage = $this->prophesize(EntityStorageInterface::class);
    $block_content_type_storage->loadMultiple()->willReturn($preexisting_types);

    $migration_plugin_manager = $this->prophesize(MigrationPluginManagerInterface::class);

    if ($core_type_migration_exists) {
      $block_type_migration = $this->prophesize(Migration::class);
      $block_type_migration->willImplement(RequirementsInterface::class);
      $block_type_migration->checkRequirements()->willReturn(function () {});

      $migration_plugin_manager
        ->createInstance(Argument::exact('block_content_type'))
        ->willReturn($block_type_migration->reveal());
    }
    else {
      $migration_plugin_manager
        ->createInstance(Argument::exact('block_content_type'))
        ->willThrow(PluginException::class);
    }

    $plugin = new BeanUniqueType([], 'bean_bundle', [], $block_content_type_storage->reveal(), $migration_plugin_manager->reveal());
    $actual = $plugin->transform($source, $this->migrateExecutable, $this->row, 'dest_prop');
    $this->assertEquals($expected, $actual);
  }

  /**
   * Test cases for ::testTransform.
   *
   * @return array[]
   *   The test cases.
   */
  public function providerTestTransform() {
    return [
      'No core migration, no preexisting bundles' => [
        'Source' => 'basic',
        'Preexisting types' => [],
        'Core type migration exists' => FALSE,
        'Expected' => 'basic',
      ],
      'No core migration, preexisting "basic" bundle' => [
        'Source' => 'basic',
        'Preexisting types' => ['basic'],
        'Core type migration exists' => FALSE,
        'Expected' => 'basic_bean_1',
      ],
      'Core migration, no preexisting bundles' => [
        'Source' => 'basic',
        'Preexisting types' => [],
        'Core type migration exists' => TRUE,
        'Expected' => 'basic_bean_1',
      ],
      'Core migration, preexisting "basic" bundle' => [
        'Source' => 'basic',
        'Preexisting types' => [],
        'Core type migration exists' => TRUE,
        'Expected' => 'basic_bean_1',
      ],
      'Core migration, lot of conflicting preexisting bundle' => [
        'Source' => 'basic',
        'Preexisting types' => [
          'basic_bean_1',
          'basic_bean_2',
          'basic_bean_4',
        ],
        'Core type migration exists' => TRUE,
        'Expected' => 'basic_bean_3',
      ],
    ];
  }

}
