<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

/**
 * Tests the "bean_title_field_instance" migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanTitleFieldInstance
 * @group bean_migrate
 */
class BeanTitleFieldInstanceTest extends BeanSourceTestBase {

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    return [
      'One of the types has a translatable title' => [
        'Source' => [
          'bean' => [
            ['type' => 'type_with_default_title'],
            ['type' => 'type_with_translatable_title'],
          ],
          'field_config_instance' => [
            [
              'field_name' => 'title_field',
              'entity_type' => 'bean',
              'bundle' => 'type_with_translatable_title',
            ],
          ],
        ],
        'Expected' => [
          [
            'type' => 'type_with_default_title',
            'title_field_exists' => 0,
            'cache_key' => '16758b5a68422ffbc9e428e2e018dbd3c09c1ff181f09d975ae82a6c695048c6',
          ],
          [
            'type' => 'type_with_translatable_title',
            'title_field_exists' => 1,
            'cache_key' => '16758b5a68422ffbc9e428e2e018dbd3c09c1ff181f09d975ae82a6c695048c6',
          ],
        ],
      ],
    ];
  }

}
