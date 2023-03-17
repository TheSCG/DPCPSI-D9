<?php

namespace Drupal\Tests\bean_migrate\Unit\Plugin\migrate\process;

use Drupal\bean_migrate\Plugin\migrate\process\BeanBlockPluginId;
use Drupal\block_content\BlockContentInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\Tests\migrate\Unit\process\MigrateProcessTestCase;
use Prophecy\Argument;

/**
 * Tests the "bean_block_plugin_id" migrate process plugin.
 *
 * @coversDefaultClass \Drupal\bean_migrate\Plugin\migrate\process\BeanBlockPluginId
 * @group bean_migrate
 */
class BeanBlockPluginIdTest extends MigrateProcessTestCase {

  /**
   * Tests that transform work as expected.
   *
   * @dataProvider providerTestTransform
   */
  public function testTransform($source, array $block_uuids, $expected) {
    $migrate_lookup = $this->prophesize(MigrateLookupInterface::class);
    $lookup_returns_result = is_array($source) && isset($block_uuids[reset($source)]);
    $block_content_uuid = $lookup_returns_result
      ? $block_uuids[reset($source)]
      : NULL;

    if ($lookup_returns_result) {
      $migrate_lookup->lookup(['bean'], $source)->willReturn([
        [
          'id' => reset($source),
          'revision_id' => next($source),
        ],
      ]);
    }
    else {
      $migrate_lookup->lookup(['bean'], $source)->willReturn([]);
    }

    $block_content_type_storage = $this->prophesize(EntityStorageInterface::class);
    if ($lookup_returns_result) {
      $block_content = $this->prophesize(BlockContentInterface::class);
      $block_content->uuid()->willReturn($block_content_uuid);

      $block_content_type_storage
        ->load(reset($source))
        ->willReturn($block_content->reveal());
    }
    else {
      $block_content_type_storage
        ->load(Argument::any())
        ->shouldNotBeCalled();
    }

    $plugin = new BeanBlockPluginId([], 'bean_bundle', [], $block_content_type_storage->reveal(), $migrate_lookup->reveal());
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
      'Source is not an array' => [
        'Source' => 'whatever',
        'Block plugin UUIDs' => [
          '1' => 'af2fdcfa-1294-4cb8-8202-b367ca3c7829',
        ],
        'Expected' => NULL,
      ],
      'Source count is 1' => [
        'Source' => [
          '1',
        ],
        'Block plugin UUIDs' => [
          '1' => 'af2fdcfa-1294-4cb8-8202-b367ca3c7829',
        ],
        'Expected' => NULL,
      ],
      'Source count is 3' => [
        'Source' => [
          '1',
          '2',
          '3',
        ],
        'Block plugin UUIDs' => [
          '1' => 'af2fdcfa-1294-4cb8-8202-b367ca3c7829',
        ],
        'Expected' => NULL,
      ],
      'Bean destination exists' => [
        'Source' => [
          // The source ID.
          '1',
          // The source revision ID.
          '2',
        ],
        'Block plugin UUIDs' => [
          '1' => 'af2fdcfa-1294-4cb8-8202-b367ca3c7829',
        ],
        'Expected' => 'block_content:af2fdcfa-1294-4cb8-8202-b367ca3c7829',
      ],
      'Bean destination not found' => [
        'Source' => [
          '3',
          '6',
        ],
        'Block plugin UUIDs' => [
          '1' => 'af2fdcfa-1294-4cb8-8202-b367ca3c7829',
        ],
        'Expected' => NULL,
      ],
    ];
  }

}
