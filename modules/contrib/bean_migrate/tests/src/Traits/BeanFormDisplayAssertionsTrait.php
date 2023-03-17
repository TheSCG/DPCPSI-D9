<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;

/**
 * Trait for testing form display configs of the migrated bean types.
 */
trait BeanFormDisplayAssertionsTrait {

  /**
   * Entity form display properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $entityFormDisplayUnconcernedProperties = [
    'uuid',
    'langcode',
  ];

  /**
   * Checks the form display of the block content type migrated from "simple".
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleFormDisplay(string $type = 'simple') {
    $form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load("block_content.{$type}.default");

    $this->assertInstanceOf(EntityFormDisplayInterface::class, $form_display);

    $expected_form_display = [
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
          'weight' => 11,
          'type' => 'text_textarea',
          'settings' => [
            'rows' => 5,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'info' => [
          'type' => 'string_textfield',
          'weight' => -5,
          'region' => 'content',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
        ],
        'title' => [
          'weight' => -9,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [],
    ];

    if (\Drupal::moduleHandler()->moduleExists('language')) {
      $expected_form_display['content']['langcode'] = [
        'type' => 'language_select',
        'weight' => 5,
        'region' => 'content',
        'settings' => [
          'include_locked' => TRUE,
        ],
        'third_party_settings' => [],
      ];
    }

    $this->assertEquals($expected_form_display, $this->getImportantEntityProperties($form_display));
  }

  /**
   * Checks the form display of the block content type migrated from "image".
   */
  public function assertBeanImageFormDisplay() {
    $form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('block_content.image.default');

    $this->assertInstanceOf(EntityFormDisplayInterface::class, $form_display);

    $expected_form_display = [
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.image',
          'field.field.block_content.image.field_image',
          'field.field.block_content.image.title',
          'image.style.thumbnail',
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
          'weight' => 11,
          'type' => 'image_image',
          'settings' => [
            'progress_indicator' => 'throbber',
            'preview_image_style' => 'thumbnail',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'info' => [
          'type' => 'string_textfield',
          'weight' => -5,
          'region' => 'content',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
        ],
        'title' => [
          'weight' => -9,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [],
    ];

    if (\Drupal::moduleHandler()->moduleExists('language')) {
      $expected_form_display['content']['langcode'] = [
        'type' => 'language_select',
        'weight' => 5,
        'region' => 'content',
        'settings' => [
          'include_locked' => TRUE,
        ],
        'third_party_settings' => [],
      ];
    }

    $this->assertEquals($expected_form_display, $this->getImportantEntityProperties($form_display));
  }

  /**
   * Checks the form display of "fully_translatable".
   */
  public function assertBeanFullyTranslatableFormDisplay() {
    $form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('block_content.fully_translatable.default');

    $this->assertInstanceOf(EntityFormDisplayInterface::class, $form_display);

    $expected_form_display = [
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
          'weight' => -5,
          'type' => 'text_textarea',
          'settings' => [
            'rows' => 5,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'field_string_translatable' => [
          'weight' => -7,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'info' => [
          'type' => 'string_textfield',
          'weight' => -5,
          'region' => 'content',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
        ],
        'title' => [
          'weight' => -9,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [],
    ];

    if (\Drupal::moduleHandler()->moduleExists('language')) {
      $expected_form_display['content']['langcode'] = [
        'type' => 'language_select',
        'weight' => 5,
        'region' => 'content',
        'settings' => [
          'include_locked' => FALSE,
        ],
        'third_party_settings' => [],
      ];
    }

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_form_display['content']['translation'] = [
        'weight' => 10,
        'settings' => [],
        'third_party_settings' => [],
        'region' => 'content',
      ];
    }

    $this->assertEquals($expected_form_display, $this->getImportantEntityProperties($form_display));
  }

  /**
   * Checks the form display of the block type migrated from "weird" bean.
   */
  public function assertBeanWeirdFormDisplay() {
    $form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->load('block_content.weird.default');

    $this->assertInstanceOf(EntityFormDisplayInterface::class, $form_display);

    $expected_form_display = [
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
          'weight' => -5,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
        'info' => [
          'type' => 'string_textfield',
          'weight' => -5,
          'region' => 'content',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
        ],
        'title' => [
          'weight' => -9,
          'type' => 'string_textfield',
          'settings' => [
            'size' => 60,
            'placeholder' => '',
          ],
          'third_party_settings' => [],
          'region' => 'content',
        ],
      ],
      'hidden' => [],
    ];

    if (\Drupal::moduleHandler()->moduleExists('language')) {
      $expected_form_display['content']['langcode'] = [
        'type' => 'language_select',
        'weight' => 19,
        'region' => 'content',
        'settings' => [
          'include_locked' => FALSE,
        ],
        'third_party_settings' => [],
      ];
    }

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_form_display['content']['translation'] = [
        'weight' => 10,
        'settings' => [],
        'third_party_settings' => [],
        'region' => 'content',
      ];
    }

    $this->assertEquals($expected_form_display, $this->getImportantEntityProperties($form_display));
  }

}
