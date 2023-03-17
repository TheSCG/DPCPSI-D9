<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\language\ContentLanguageSettingsInterface;

/**
 * Trait for testing language content settings of migrated bean types.
 */
trait BeanTranslationSettingsAssertionsTrait {

  /**
   * Entity form display properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $languageContentSettingsUnconcernedProperties = [
    'uuid',
    'langcode',
  ];

  /**
   * Checks the settings of the block content type migrated from "simple".
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBeanSimpleTranslationSettings(string $type = 'simple') {
    $settings = \Drupal::entityTypeManager()
      ->getStorage('language_content_settings')
      ->load("block_content.{$type}");

    $this->assertInstanceOf(ContentLanguageSettingsInterface::class, $settings);

    $expected_settings = [
      'id' => "block_content.{$type}",
      'target_entity_type_id' => 'block_content',
      'target_bundle' => $type,
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          "block_content.type.{$type}",
        ],
      ],
      'default_langcode' => 'und',
      'language_alterable' => FALSE,
    ];

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_settings['dependencies']['module'] = [
        'content_translation',
      ];
      $expected_settings['third_party_settings']['content_translation'] = [
        'enabled' => FALSE,
        'bundle_settings' => [
          'untranslatable_fields_hide' => '0',
        ],
      ];
    }

    $this->assertEquals($expected_settings, $this->getImportantEntityProperties($settings));
  }

  /**
   * Checks the settings of the block content type migrated from "image".
   */
  public function assertBeanImageTranslationSettings() {
    $settings = \Drupal::entityTypeManager()
      ->getStorage('language_content_settings')
      ->load('block_content.image');

    $this->assertInstanceOf(ContentLanguageSettingsInterface::class, $settings);

    $expected_settings = [
      'id' => 'block_content.image',
      'target_entity_type_id' => 'block_content',
      'target_bundle' => 'image',
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.image',
        ],
      ],
      'default_langcode' => 'und',
      'language_alterable' => FALSE,
    ];

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_settings['dependencies']['module'] = [
        'content_translation',
      ];
      $expected_settings['third_party_settings']['content_translation'] = [
        'enabled' => FALSE,
        'bundle_settings' => [
          'untranslatable_fields_hide' => '0',
        ],
      ];
    }

    $this->assertEquals($expected_settings, $this->getImportantEntityProperties($settings));
  }

  /**
   * Checks the settings of block type migrated from "fully_translatable".
   */
  public function assertBeanFullyTranslatableTranslationSettings() {
    $settings = \Drupal::entityTypeManager()
      ->getStorage('language_content_settings')
      ->load('block_content.fully_translatable');

    $this->assertInstanceOf(ContentLanguageSettingsInterface::class, $settings);

    $expected_settings = [
      'id' => 'block_content.fully_translatable',
      'target_entity_type_id' => 'block_content',
      'target_bundle' => 'fully_translatable',
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.fully_translatable',
        ],
      ],
      'default_langcode' => 'site_default',
      'language_alterable' => TRUE,
    ];

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_settings['dependencies']['module'] = [
        'content_translation',
      ];
      $expected_settings['third_party_settings']['content_translation'] = [
        'enabled' => TRUE,
        'bundle_settings' => [
          'untranslatable_fields_hide' => '0',
        ],
      ];
    }

    $this->assertEquals($expected_settings, $this->getImportantEntityProperties($settings));
  }

  /**
   * Checks the settings of block type migrated from "weird" bean type.
   */
  public function assertBeanWeirdTranslationSettings() {
    $settings = \Drupal::entityTypeManager()
      ->getStorage('language_content_settings')
      ->load('block_content.weird');

    $this->assertInstanceOf(ContentLanguageSettingsInterface::class, $settings);

    $expected_settings = [
      'id' => 'block_content.weird',
      'target_entity_type_id' => 'block_content',
      'target_bundle' => 'weird',
      'status' => TRUE,
      'dependencies' => [
        'config' => [
          'block_content.type.weird',
        ],
      ],
      'default_langcode' => 'site_default',
      'language_alterable' => TRUE,
    ];

    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      $expected_settings['dependencies']['module'] = [
        'content_translation',
      ];
      $expected_settings['third_party_settings']['content_translation'] = [
        'enabled' => TRUE,
        'bundle_settings' => [
          'untranslatable_fields_hide' => '0',
        ],
      ];
    }

    $this->assertEquals($expected_settings, $this->getImportantEntityProperties($settings));
  }

}
