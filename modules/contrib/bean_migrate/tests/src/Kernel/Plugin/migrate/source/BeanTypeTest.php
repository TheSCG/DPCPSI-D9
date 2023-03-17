<?php

namespace Drupal\Tests\bean_migrate\Kernel\Plugin\migrate\source;

/**
 * Tests the bean type migrate source plugin.
 *
 * @covers \Drupal\bean_migrate\Plugin\migrate\source\BeanType
 * @group bean_migrate
 */
class BeanTypeTest extends BeanSourceTestBase {

  /**
   * {@inheritdoc}
   */
  public function providerSource() {
    $test_cases = [
      'No bean_type table' => [
        'Source' => [
          'bean' => [
            ['type' => 'complicated'],
            ['type' => 'simple'],
            ['type' => 'complicated'],
            ['type' => 'simple'],
          ],
        ],
        'Expected' => [
          [
            'type' => 'complicated',
            'cache_key' => '876b6787922b88cbd55f57f4d20bdc6936e393073e03461a9c73f8bd30543191',
          ],
          [
            'type' => 'simple',
            'cache_key' => '876b6787922b88cbd55f57f4d20bdc6936e393073e03461a9c73f8bd30543191',
          ],
        ],
      ],
      'With incomplete bean_type table' => [
        'Source' => [
          'bean' => [
            ['type' => 'simple'],
            ['type' => 'complicated'],
            ['type' => 'simple'],
            ['type' => 'complicated'],
            ['type' => 'complicated'],
          ],
          'bean_type' => [
            [
              'name' => 'complicated',
              'label' => 'Complete label',
              'description' => 'Description for complete',
            ],
          ],
        ],
        'Expected' => [
          [
            'type' => 'complicated',
            'label' => 'Complete label',
            'description' => 'Description for complete',
            'cache_key' => '876b6787922b88cbd55f57f4d20bdc6936e393073e03461a9c73f8bd30543191',
          ],
          [
            'type' => 'simple',
            'label' => NULL,
            'description' => NULL,
            'cache_key' => '876b6787922b88cbd55f57f4d20bdc6936e393073e03461a9c73f8bd30543191',
          ],
        ],
      ],
    ];
    $test_cases['With filtering applied'] = [
      'Source' => $test_cases['No bean_type table']['Source'],
      'Expected' => [
        [
          'type' => 'simple',
          'cache_key' => '3b50b932a2758ec5a8de20048dcd1d9d3aff830b7cd0867c9b77bf5ec027c6f8',
        ],
      ],
      'Count' => 1,
      'Plugin config' => [
        'type' => 'simple',
      ],
    ];

    return $test_cases;
  }

}
