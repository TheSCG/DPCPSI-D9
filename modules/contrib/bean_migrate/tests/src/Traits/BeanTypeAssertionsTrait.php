<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\block_content\Entity\BlockContentType;

/**
 * Trait for bean type related assertions.
 */
trait BeanTypeAssertionsTrait {

  /**
   * List of block type properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $blockContentTypeUnconcernedProperties = [
    'uuid',
    'langcode',
  ];

  /**
   * Checks whether the "image" bean type was successfully migrated.
   */
  public function assertBeanImageBlockContentType() {
    $block_content_type = \Drupal::entityTypeManager()
      ->getStorage('block_content_type')
      ->load('image');
    assert($block_content_type instanceof BlockContentType);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [],
      'id' => 'image',
      'label' => 'Image',
      'revision' => 1,
      'description' => 'A type for referencing images.',
    ], $this->getImportantEntityProperties($block_content_type));
  }

  /**
   * Checks whether the "simple" (basic) bean type was successfully migrated.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleBlockContentType(string $type = 'simple') {
    $block_content_type = \Drupal::entityTypeManager()
      ->getStorage('block_content_type')
      ->load($type);
    assert($block_content_type instanceof BlockContentType);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [],
      'id' => $type,
      'label' => 'Simple',
      'revision' => 1,
      'description' => 'A simple type for storing a short formatted text.',
    ], $this->getImportantEntityProperties($block_content_type));
  }

}
