<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\field\FieldConfigInterface;

/**
 * Trait for bean field related assertions.
 */
trait BeanFieldConfigAssertionsTrait {

  /**
   * List of block type properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $fieldConfigUnconcernedProperties = [
    'uuid',
    'langcode',
  ];

  /**
   * Checks the "body" field of "page" node type.
   */
  public function assertNodeBodyFieldInstance() {
    $field_instance = $this->container->get('entity_type.manager')->getStorage('field_config')->load('node.page.node_body');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'field.storage.node.node_body',
          'node.type.page',
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => 'node.page.node_body',
      'label' => 'Body',
      'description' => '',
      'field_name' => 'node_body',
      'entity_type' => 'node',
      'bundle' => 'page',
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'display_summary' => TRUE,
        'required_summary' => FALSE,
      ],
      'field_type' => 'text_with_summary',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "field_image" field instance migrated for the "image" type.
   */
  public function assertBeanImageImageFieldInstance() {
    $field_instance = $this->container->get('entity_type.manager')->getStorage('field_config')->load('block_content.image.field_image');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.image',
          'field.storage.block_content.field_image',
        ],
        'module' => [
          'image',
        ],
      ],
      'id' => 'block_content.image.field_image',
      'label' => 'Image',
      'description' => '',
      'field_name' => 'field_image',
      'entity_type' => 'block_content',
      'bundle' => 'image',
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [
        'file_directory' => 'images',
        'file_extensions' => 'png gif jpg jpeg',
        'max_filesize' => '',
        'max_resolution' => '',
        'min_resolution' => '',
        'alt_field' => FALSE,
        'title_field' => FALSE,
        'default_image' => [
          'alt' => '',
          'title' => '',
          'width' => NULL,
          'height' => NULL,
          'uuid' => '',
        ],
        'alt_field_required' => TRUE,
        'title_field_required' => FALSE,
        'handler' => 'default:file',
        'handler_settings' => [],
      ],
      'field_type' => 'image',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "title" field instance migrated for the "image" type.
   */
  public function assertBeanImageTitleFieldInstance() {
    $image_title_field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.image.title');
    assert($image_title_field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.image',
          'field.storage.block_content.title',
        ],
      ],
      'id' => 'block_content.image.title',
      'label' => 'Title',
      'description' => 'The Title of the block.',
      'field_name' => 'title',
      'entity_type' => 'block_content',
      'bundle' => 'image',
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($image_title_field_instance));
  }

  /**
   * Checks the "field_body" field instance migrated for the "simple" type.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleBodyFieldInstance(string $type = 'simple') {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load("block_content.{$type}.field_body");
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          "block_content.type.{$type}",
          'field.storage.block_content.field_body',
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => "block_content.{$type}.field_body",
      'label' => 'Body',
      'description' => '',
      'field_name' => 'field_body',
      'entity_type' => 'block_content',
      'bundle' => $type,
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'text_long',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "title" field instance migrated for the "simple" type.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleTitleFieldInstance(string $type = 'simple') {
    $simple_title_field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load("block_content.{$type}.title");
    assert($simple_title_field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          "block_content.type.{$type}",
          'field.storage.block_content.title',
        ],
      ],
      'id' => "block_content.{$type}.title",
      'label' => 'Title',
      'description' => 'The Title of the block.',
      'field_name' => 'title',
      'entity_type' => 'block_content',
      'bundle' => $type,
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($simple_title_field_instance));
  }

  /**
   * Checks the "translatable body" field instance of "fully_translatable".
   */
  public function assertBeanFullyTranslatableBodyFieldInstance() {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.fully_translatable.field_body_translatable');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.fully_translatable',
          'field.storage.block_content.field_body_translatable',
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => 'block_content.fully_translatable.field_body_translatable',
      'label' => 'Translatable body',
      'description' => '',
      'field_name' => 'field_body_translatable',
      'entity_type' => 'block_content',
      'bundle' => 'fully_translatable',
      'required' => FALSE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'text_long',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "title" field instance migrated for "fully_translatable".
   *
   * For the "fully_translatable" type, this have to be marked as translatable.
   */
  public function assertBeanFullyTranslatableTitleFieldInstance() {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.fully_translatable.title');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.fully_translatable',
          'field.storage.block_content.title',
        ],
      ],
      'id' => 'block_content.fully_translatable.title',
      'label' => 'Title',
      'description' => 'The Title of the block.',
      'field_name' => 'title',
      'entity_type' => 'block_content',
      'bundle' => 'fully_translatable',
      'required' => FALSE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "translatable string" field instance of "fully_translatable".
   */
  public function assertBeanFullyTranslatableStringFieldInstance() {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.fully_translatable.field_string_translatable');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.fully_translatable',
          'field.storage.block_content.field_string_translatable',
        ],
      ],
      'id' => 'block_content.fully_translatable.field_string_translatable',
      'label' => 'Translatable string',
      'description' => '',
      'field_name' => 'field_string_translatable',
      'entity_type' => 'block_content',
      'bundle' => 'fully_translatable',
      'required' => FALSE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "title" field instance migrated for "weird".
   */
  public function assertBeanWeirdTitleFieldInstance() {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.weird.title');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.weird',
          'field.storage.block_content.title',
        ],
      ],
      'id' => 'block_content.weird.title',
      'label' => 'Title',
      'description' => 'The Title of the block.',
      'field_name' => 'title',
      'entity_type' => 'block_content',
      'bundle' => 'weird',
      'required' => FALSE,
      'translatable' => FALSE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($field_instance));
  }

  /**
   * Checks the "translatable string" field instance of "fully_translatable".
   */
  public function assertBeanWeirdStringFieldInstance() {
    $field_instance = \Drupal::entityTypeManager()
      ->getStorage('field_config')
      ->load('block_content.weird.field_string_translatable');
    assert($field_instance instanceof FieldConfigInterface);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.weird',
          'field.storage.block_content.field_string_translatable',
        ],
      ],
      'id' => 'block_content.weird.field_string_translatable',
      'label' => 'Translatable string',
      'description' => '',
      'field_name' => 'field_string_translatable',
      'entity_type' => 'block_content',
      'bundle' => 'weird',
      'required' => FALSE,
      'translatable' => TRUE,
      'default_value' => [],
      'default_value_callback' => '',
      'settings' => [],
      'field_type' => 'string',
    ], $this->getImportantEntityProperties($field_instance));
  }

}
