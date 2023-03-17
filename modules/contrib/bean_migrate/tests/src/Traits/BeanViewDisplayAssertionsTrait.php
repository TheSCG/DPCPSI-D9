<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;

/**
 * Trait for testing view display configs of the migrated bean types.
 */
trait BeanViewDisplayAssertionsTrait {

  /**
   * Entity form display properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $entityViewDisplayUnconcernedProperties = [
    'uuid',
    'langcode',
  ];

  /**
   * Checks the view displays of the block content type migrated from "simple".
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleViewDisplays(string $type = 'simple') {
    // Test the default display.
    $view_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("block_content.{$type}.default");

    $this->assertInstanceOf(EntityViewDisplayInterface::class, $view_display);

    $expected = [
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          "block_content.type.{$type}",
          "field.field.block_content.{$type}.field_body",
          "field.field.block_content.{$type}.title",
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => "block_content.{$type}.default",
      'targetEntityType' => 'block_content',
      'bundle' => $type,
      'mode' => 'default',
      'content' => [
        'field_body' => [
          'label' => 'hidden',
          'weight' => 0,
          'type' => 'text_default',
          'settings' => [],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'title' => [
          'type' => 'string',
          'weight' => -9,
          'label' => 'hidden',
          'settings' => [
            'link_to_entity' => FALSE,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [],
    ];
    if ($this->isMultilingualTest) {
      $expected['hidden'] += [
        'langcode' => TRUE,
      ];
    }
    $this->assertEquals($expected, $this->getImportantEntityProperties($view_display));

    // Test the compact display.
    $view_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load("block_content.{$type}.compact");

    $this->assertInstanceOf(EntityViewDisplayInterface::class, $view_display);

    $expected = [
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          "block_content.type.{$type}",
          'core.entity_view_mode.block_content.compact',
          "field.field.block_content.{$type}.field_body",
          "field.field.block_content.{$type}.title",
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => "block_content.{$type}.compact",
      'targetEntityType' => 'block_content',
      'bundle' => $type,
      'mode' => 'compact',
      'content' => [
        'field_body' => [
          'label' => 'hidden',
          'weight' => 0,
          'type' => 'text_trimmed',
          'settings' => [
            'trim_length' => 100,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [
        'title' => TRUE,
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected['hidden'] += [
        'langcode' => TRUE,
      ];
    }
    $this->assertEquals($expected, $this->getImportantEntityProperties($view_display));
  }

  /**
   * Checks the view display of the block content type migrated from "image".
   */
  public function assertBeanImageViewDisplays() {
    $view_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('block_content.image.default');

    $this->assertInstanceOf(EntityViewDisplayInterface::class, $view_display);

    $expected = [
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.image',
          'field.field.block_content.image.field_image',
          'field.field.block_content.image.title',
          'image.style.medium',
        ],
        'module' => [
          'image',
        ],
      ],
      'id' => 'block_content.image.default',
      'targetEntityType' => 'block_content',
      'bundle' => 'image',
      'mode' => 'default',
      'content' => [
        'field_image' => [
          'label' => 'hidden',
          'weight' => 0,
          'type' => 'image',
          'settings' => [
            'image_style' => 'medium',
            'image_link' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [
        'title' => TRUE,
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected['hidden'] += [
        'langcode' => TRUE,
      ];
    }
    if (static::imageLoadingSettingsPresent()) {
      $expected['content']['field_image']['settings']['image_loading'] = [
        'attribute' => 'lazy',
      ];
    }
    $this->assertEquals($expected, $this->getImportantEntityProperties($view_display));

    $this->assertEmpty(
      \Drupal::entityTypeManager()
        ->getStorage('entity_view_display')
        ->load('block_content.image.compact')
    );
  }

  /**
   * Checks the view display of the block content type migrated from "ft".
   */
  public function assertBeanFullyTranslatableViewDisplay() {
    $view_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('block_content.fully_translatable.default');

    $this->assertInstanceOf(EntityViewDisplayInterface::class, $view_display);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.fully_translatable',
          'field.field.block_content.fully_translatable.field_body_translatable',
          'field.field.block_content.fully_translatable.field_string_translatable',
          'field.field.block_content.fully_translatable.title',
        ],
        'module' => [
          'text',
        ],
      ],
      'id' => 'block_content.fully_translatable.default',
      'targetEntityType' => 'block_content',
      'bundle' => 'fully_translatable',
      'mode' => 'default',
      'content' => [
        'field_body_translatable' => [
          'label' => 'above',
          'weight' => 1,
          'type' => 'text_default',
          'settings' => [],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'field_string_translatable' => [
          'label' => 'inline',
          'weight' => 0,
          'type' => 'string',
          'settings' => [
            'link_to_entity' => FALSE,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'title' => [
          'type' => 'string',
          'weight' => -9,
          'label' => 'hidden',
          'settings' => [
            'link_to_entity' => FALSE,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [
        'langcode' => TRUE,
      ],
    ], $this->getImportantEntityProperties($view_display));

    $this->assertEmpty(
      \Drupal::entityTypeManager()
        ->getStorage('entity_view_display')
        ->load('block_content.fully_translatable.compact')
    );
  }

  /**
   * Checks the view display of the block content type migrated from "weird".
   */
  public function assertBeanWeirdViewDisplay() {
    $view_display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('block_content.weird.default');

    $this->assertInstanceOf(EntityViewDisplayInterface::class, $view_display);

    $this->assertEquals([
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.weird',
          'field.field.block_content.weird.field_string_translatable',
          'field.field.block_content.weird.title',
        ],
      ],
      'id' => 'block_content.weird.default',
      'targetEntityType' => 'block_content',
      'bundle' => 'weird',
      'mode' => 'default',
      'content' => [
        'field_string_translatable' => [
          'label' => 'above',
          'weight' => 0,
          'type' => 'string',
          'settings' => [
            'link_to_entity' => FALSE,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'title' => [
          'type' => 'string',
          'weight' => -9,
          'label' => 'hidden',
          'settings' => [
            'link_to_entity' => FALSE,
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [
        'langcode' => TRUE,
      ],
    ], $this->getImportantEntityProperties($view_display));

    $this->assertEmpty(
      \Drupal::entityTypeManager()
        ->getStorage('entity_view_display')
        ->load('block_content.weird.compact')
    );
  }

  /**
   * Checks that image_loading config option is available on image formatters.
   *
   * @return bool
   *   Whether image_loading config option is available on image formatters.
   */
  protected static function imageLoadingSettingsPresent(): bool {
    return version_compare(\Drupal::VERSION, '9.4.0-dev', 'ge');
  }

}
