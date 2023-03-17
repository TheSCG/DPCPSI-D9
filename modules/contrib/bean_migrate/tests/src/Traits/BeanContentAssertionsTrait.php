<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\Core\Entity\TranslatableInterface;

/**
 * Trait for bean content related assertions.
 */
trait BeanContentAssertionsTrait {

  /**
   * List of block type properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $blockContentUnconcernedProperties = [
    'id',
    'revision_id',
  ];

  /**
   * Checks the block_content entity which was migrated from Bean #1.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBean1(string $type = 'simple') {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #1']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $other_revisions = $this->loadNonDefaultEntityRevisions($default_block_revision);
    // Bean block #1 should only have one revision.
    $this->assertCount(0, $other_revisions);

    $expected = [
      'uuid' => [['value' => 'f5c783c4-2fa5-48a2-9c09-5c43e4faea8c']],
      'type' => [['target_id' => $type]],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => '1611055931']],
      'revision_user' => [['target_id' => '1']],
      'revision_log' => [],
      'status' => [['value' => '1']],
      'info' => [['value' => 'Admin label of Bean #1']],
      'changed' => [['value' => '1611055931']],
      'reusable' => [['value' => '1']],
      'default_langcode' => [['value' => '1']],
      'revision_default' => [['value' => '1']],
      'revision_translation_affected' => [['value' => 1]],
      'title' => [['value' => 'Title of Bean block #1']],
      'field_body' => [
        [
          'value' => 'Copy of Bean block #1: a simple bean block with default view mode and with a filtered text format.',
          'format' => 'filtered_html',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }

    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties($default_block_revision));
  }

  /**
   * Checks the block_content entity which was migrated from Bean #2.
   *
   * Also checks the previous revision of the destination entity.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBean2(string $type = 'simple') {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #2']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $other_revisions = $this->loadNonDefaultEntityRevisions($default_block_revision);
    // Bean block #2 should only have one other revision.
    $this->assertCount(1, $other_revisions);

    // Check the current revision.
    $expected = [
      'uuid' => [['value' => '8568da7e-9325-4421-8473-fe1d5d14af6a']],
      'type' => [['target_id' => $type]],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => 1611056213]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [['value' => 'A revision log message for the second revision of Bean #2.']],
      'status' => [['value' => '1']],
      'info' => [['value' => 'Admin label of Bean #2']],
      'changed' => [['value' => 1611056294]],
      'reusable' => [['value' => '1']],
      'default_langcode' => [['value' => '1']],
      'revision_default' => [['value' => '1']],
      'revision_translation_affected' => [['value' => '1']],
      'title' => [['value' => 'Title of Bean block #2 (rev 2)']],
      'field_body' => [
        [
          'value' => 'Copy of Bean block #2 (rev 2): view_mode changed from compact to default; text format is still plain text.',
          'format' => 'plain_text',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }
    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties($default_block_revision));

    // Test the previous revision.
    $expected = [
      'uuid' => [['value' => '8568da7e-9325-4421-8473-fe1d5d14af6a']],
      'type' => [['target_id' => $type]],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => 1611056213]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [],
      'status' => [['value' => '1']],
      'info' => [['value' => 'Admin label of Bean #2']],
      'changed' => [['value' => 1611056213]],
      'reusable' => [['value' => '1']],
      'default_langcode' => [['value' => '1']],
      'revision_default' => [['value' => '1']],
      'revision_translation_affected' => [['value' => '1']],
      'title' => [['value' => 'Title of Bean block #2 (rev 1)']],
      'field_body' => [
        [
          'value' => 'Copy of Bean block #2 (rev 1): compact view_mode, plain text format.',
          'format' => 'plain_text',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }
    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties(reset($other_revisions)));
  }

  /**
   * Checks the block_content entity which was migrated from Bean #3 (image).
   */
  public function assertBean3() {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #3']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $other_revisions = $this->loadNonDefaultEntityRevisions($default_block_revision);
    // Bean block #3 shouldn't have other revisions.
    $this->assertCount(0, $other_revisions);

    $expected = [
      'uuid' => [['value' => '657e9ce8-a3a5-414e-ae1a-0e11208abc4e']],
      'type' => [['target_id' => 'image']],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => 1611056822]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [],
      'status' => [['value' => '1']],
      'info' => [['value' => 'Admin label of Bean #3']],
      'changed' => [['value' => 1611056822]],
      'reusable' => [['value' => '1']],
      'default_langcode' => [['value' => '1']],
      'revision_default' => [['value' => '1']],
      'revision_translation_affected' => [['value' => '1']],
      'title' => [['value' => 'Yellow image']],
      'field_image' => [
        [
          'target_id' => 1,
          'alt' => '',
          'title' => '',
          'width' => '640',
          'height' => '400',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }
    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties($default_block_revision));
  }

  /**
   * Checks the block_content entity which was migrated from Bean #4.
   *
   * Also verifies the data of the next (but still unpublished) revision of the
   * destination block_content entity.
   *
   * @param string $type
   *   The ID of the migrated "simple" bean type.
   */
  public function assertBean4(string $type = 'simple') {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #4']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $other_revisions = $this->loadNonDefaultEntityRevisions($default_block_revision);
    // Bean block #4 should have a newer, but non-default revision.
    $this->assertCount(1, $other_revisions);

    // Check the current revision.
    $expected = [
      'uuid' => [['value' => '9b693dcc-74c6-402c-8078-49225b1d6820']],
      'type' => [['target_id' => $type]],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => 1611057740]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [],
      'status' => [['value' => '1']],
      'info' => [['value' => 'Admin label of Bean #4']],
      'changed' => [['value' => 1611057740]],
      'reusable' => [['value' => '1']],
      'default_langcode' => [['value' => '1']],
      'revision_default' => [['value' => '1']],
      'revision_translation_affected' => [['value' => '1']],
      'title' => [['value' => 'Bean #4 (rev 1, default)']],
      'field_body' => [
        [
          'value' => 'Body of Bean #4 (rev 1)',
          'format' => 'plain_text',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }
    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties($default_block_revision));

    // Check the next revision.
    $expected = [
      'uuid' => [['value' => '9b693dcc-74c6-402c-8078-49225b1d6820']],
      'type' => [['target_id' => $type]],
      'langcode' => [['value' => 'und']],
      'revision_created' => [['value' => 1611057740]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [['value' => 'New revision for Bean #4, but leaving the previous as default.']],
      'status' => [['value' => 0]],
      'info' => [['value' => 'Admin label of Bean #4']],
      'changed' => [['value' => 1611057797]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_default' => [['value' => 0]],
      'revision_translation_affected' => [['value' => 1]],
      'title' => [['value' => 'Bean #4 (rev 2, non-default)']],
      'field_body' => [
        [
          'value' => 'Body of Bean #4 (rev 2)',
          'format' => 'plain_text',
        ],
      ],
    ];
    if ($this->isMultilingualTest) {
      $expected += [
        'content_translation_source' => [['value' => 'und']],
        'content_translation_outdated' => [['value' => 0]],
        'content_translation_uid' => [],
        'content_translation_created' => [],
      ];
    }
    if (!$this->beanUuidsShouldBeMigrated()) {
      unset($expected['uuid']);
    }

    $this->assertEquals($expected, $this->getImportantEntityProperties(reset($other_revisions)));
  }

  /**
   * Checks the block_content entity migrated from Bean #5 (fully_Translatable).
   */
  public function assertBean5() {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Fully translatable Bean #5']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $this->assertInstanceOf(TranslatableInterface::class, $default_block_revision);

    // Bean 5 (its default language is Icelandic) is translated to English.
    $this->assertCount(1, $default_block_revision->getTranslationLanguages(FALSE));

    $expected_default = [
      'uuid' => [['value' => '0fa43981-afa8-4049-8f05-75094ff400d2']],
      'type' => [['target_id' => 'fully_translatable']],
      'langcode' => [['value' => 'is']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Admin – Fully translatable Bean #5']],
      'changed' => [['value' => 1611753589]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_created' => [['value' => 1611753420]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [
        [
          'value' => 'New published IS default revision – EN translation should be updated.',
        ],
      ],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'und']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1611753420]],
      'title' => [['value' => '[IS (default)] Bean #5 Title - new published revision']],
      'field_string_translatable' => [
        [
          'value' => '[IS (default)] Bean #5 Translatable string - new published revision',
        ],
      ],
      'field_body_translatable' => [
        [
          'value' => '[IS (default)] Bean #5 Translatable body - new published revision.',
          'format' => 'filtered_html',
        ],
      ],
    ];
    $this->assertEquals($expected_default, $this->getImportantEntityProperties($default_block_revision->getUntranslated()));

    $expected_english = [
      'langcode' => [['value' => 'en']],
      'default_langcode' => [['value' => 0]],
      'title' => [['value' => '[EN] Bean #5 Title']],
      'content_translation_source' => [['value' => 'is']],
      'content_translation_created' => [['value' => 1611753499]],
      'content_translation_outdated' => [['value' => 1]],
      'field_string_translatable' => [
        [
          'value' => '[EN] Bean #5 Translatable string',
        ],
      ],
      'field_body_translatable' => [
        [
          'value' => '[EN] Bean #5 Translatable body.',
          'format' => 'filtered_html',
        ],
      ],
    ] + $expected_default;
    $this->assertEquals($expected_english, $this->getImportantEntityProperties($default_block_revision->getTranslation('en')));
  }

  /**
   * Checks the block_content entity migrated from Bean #6 (weird).
   */
  public function assertBean6() {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Weird Bean #6']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $this->assertInstanceOf(TranslatableInterface::class, $default_block_revision);

    // Bean 6 (its default language is English) is translated to Icelandic.
    $this->assertCount(1, $default_block_revision->getTranslationLanguages(FALSE));

    $expected_default = [
      'uuid' => [['value' => '423a37e7-e3fa-4c07-9d98-dd15cd887880']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'en']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Admin – Weird Bean #6']],
      'changed' => [['value' => 1611753825]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_created' => [['value' => 1611753793]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'und']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1611753793]],
      'title' => [['value' => 'Bean #6 Title']],
      'field_string_translatable' => [
        [
          'value' => '[EN (default)] Bean #6 Translatable string',
        ],
      ],
    ];
    $this->assertEquals($expected_default, $this->getImportantEntityProperties($default_block_revision->getUntranslated()));

    $expected_icelandic = [
      'langcode' => [['value' => 'is']],
      'default_langcode' => [['value' => 0]],
      'content_translation_source' => [['value' => 'en']],
      'content_translation_created' => [['value' => 1611753825]],
      'field_string_translatable' => [
        [
          'value' => '[IS] Bean #6 Translatable string',
        ],
      ],
    ] + $expected_default;
    $this->assertEquals($expected_icelandic, $this->getImportantEntityProperties($default_block_revision->getTranslation('is')));
  }

  /**
   * Checks the block_content entity migrated from Bean #7 (weird).
   */
  public function assertBean7() {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Weird Bean #7']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $this->assertInstanceOf(TranslatableInterface::class, $default_block_revision);

    $this->assertCount(0, $default_block_revision->getTranslationLanguages(FALSE));
    $this->assertEquals([
      'uuid' => [['value' => '71e00b2f-4487-4e6f-90e5-15ac765d15a8']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'is']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Admin – Weird Bean #7']],
      'changed' => [['value' => 1611754018]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_created' => [['value' => 1611754018]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'und']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1611754018]],
      'title' => [['value' => 'Bean #7 Title']],
      'field_string_translatable' => [
        [
          'value' => '[IS] Bean #7 Translatable string',
        ],
      ],
    ], $this->getImportantEntityProperties($default_block_revision));
  }

  /**
   * Checks the block_content entity migrated from Bean #8 (weird).
   */
  public function assertBean8() {
    $blocks = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Bean #8 (new HU default)']);
    $this->assertCount(1, $blocks);
    $default_block_revision = reset($blocks);
    $this->assertInstanceOf(TranslatableInterface::class, $default_block_revision);

    // Checking previous revisions.
    $other_revisions = $this->loadNonDefaultEntityRevisions($default_block_revision);
    // Bean #8 should have 4 other versions.
    $this->assertCount(4, $other_revisions);

    // The first revision does not have any translations. Its language is HU.
    $revision_to_check = array_shift($other_revisions);
    $first_revision = [
      'uuid' => [['value' => '0a4b1a61-1e87-4cb9-9a41-1080fd8af949']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'hu']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Bean #8 (HU default)']],
      'changed' => [['value' => 1612179332]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_created' => [['value' => 1612179311]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [
        [
          'value' => 'Initial revision',
        ],
      ],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'und']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1612179332]],
      'title' => [['value' => 'Bean #8 title']],
      'field_string_translatable' => [
        [
          'value' => 'Bean #8 hu string (first HU version)',
        ],
      ],
    ];
    $this->assertEquals($first_revision, $this->getImportantEntityProperties($revision_to_check->getUntranslated()));
    // No other languages.
    $this->assertCount(0, $revision_to_check->getTranslationLanguages(FALSE));

    // Second revision was created while the Icelandic translation was added.
    $revision_to_check = array_shift($other_revisions);
    $second_revision = [
      'changed' => [['value' => '1612179433']],
      'revision_log' => [['value' => 'First IS translation by editor']],
      'revision_translation_affected' => [],
    ] + $first_revision;
    $this->assertEquals($second_revision, $this->getImportantEntityProperties($revision_to_check->getUntranslated()));
    $this->assertCount(1, $revision_to_check->getTranslationLanguages(FALSE));
    // The Icelandic translation - this was created by the "editor" user
    // (uid 2).
    $second_revision_icelandic = [
      'uuid' => [['value' => '0a4b1a61-1e87-4cb9-9a41-1080fd8af949']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'is']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Bean #8 (HU default)']],
      'changed' => [['value' => 1612179433]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 0]],
      'revision_created' => [['value' => 1612179311]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [
        [
          'value' => 'First IS translation by editor',
        ],
      ],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'hu']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 2]],
      'content_translation_created' => [['value' => 1612179433]],
      'title' => [['value' => 'Bean #8 title']],
      'field_string_translatable' => [
        [
          'value' => 'Bean #8 IS string (first IS transation)',
        ],
      ],
    ];
    $this->assertEquals($second_revision_icelandic, $this->getImportantEntityProperties($revision_to_check->getTranslation('is')));

    // Third revision was created when an English translation was added. The
    // English translation's source is the Icelandic translation.
    $revision_to_check = array_shift($other_revisions);
    $third_revision = [
      'changed' => [['value' => '1612179503']],
      'revision_log' => [['value' => 'First EN translation from IS.']],
    ] + $second_revision;
    $this->assertEquals($third_revision, $this->getImportantEntityProperties($revision_to_check->getUntranslated()));
    $this->assertCount(2, $revision_to_check->getTranslationLanguages(FALSE));
    // The Icelandic translation is like what was created by the "editor" user
    // (uid 2).
    $third_revision_icelandic = [
      'changed' => [['value' => 1612179503]],
      'revision_log' => [['value' => 'First EN translation from IS.']],
      'revision_translation_affected' => [],
    ] + $second_revision_icelandic;
    $this->assertEquals($third_revision_icelandic, $this->getImportantEntityProperties($revision_to_check->getTranslation('is')));
    // The new English translation.
    $third_revision_english = [
      'uuid' => [['value' => '0a4b1a61-1e87-4cb9-9a41-1080fd8af949']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'en']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Bean #8 (HU default)']],
      'changed' => [['value' => 1612179503]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 0]],
      'revision_created' => [['value' => 1612179311]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [
        [
          'value' => 'First EN translation from IS.',
        ],
      ],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_source' => [['value' => 'is']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 2]],
      'content_translation_created' => [['value' => 1612179503]],
      'title' => [['value' => 'Bean #8 title']],
      'field_string_translatable' => [
        [
          'value' => 'Bean #8 IS string (first EN translation created from IS)',
        ],
      ],
    ];
    $this->assertEquals($third_revision_english, $this->getImportantEntityProperties($revision_to_check->getTranslation('en')));

    // The fourth revision is an updated (default) hungarian version. Other
    // translations should be flagged as outdated.
    $revision_to_check = array_shift($other_revisions);
    $fourth_revision = [
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179591]],
      'revision_log' => [
        [
          'value' => 'New HU (default) version by admin, other translations are outdated.',
        ],
      ],
      'revision_translation_affected' => [['value' => 1]],
      'field_string_translatable' => [
        [
          'value' => 'New Bean #8 hu string (second, last HU version)',
        ],
      ],
    ] + $third_revision;
    $this->assertEquals($fourth_revision, $this->getImportantEntityProperties($revision_to_check->getUntranslated()));
    $this->assertCount(2, $revision_to_check->getTranslationLanguages(FALSE));
    // Icelandic should be outdated.
    $fourth_revision_icelandic = [
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179591]],
      'revision_log' => [['value' => 'New HU (default) version by admin, other translations are outdated.']],
      'revision_translation_affected' => [['value' => 1]],
      'content_translation_outdated' => [['value' => 1]],
    ] + $third_revision_icelandic;
    $this->assertEquals($fourth_revision_icelandic, $this->getImportantEntityProperties($revision_to_check->getTranslation('is')));
    // English should be outdated.
    $fourth_revision_english = [
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179591]],
      'revision_log' => [['value' => 'New HU (default) version by admin, other translations are outdated.']],
      'content_translation_outdated' => [['value' => 1]],
    ] + $third_revision_english;
    $this->assertEquals($fourth_revision_english, $this->getImportantEntityProperties($revision_to_check->getTranslation('en')));

    // The fifth (current) revision. The english translation was updated; only
    // the Icelandic translation is outdated.
    $this->assertEquals([
      'uuid' => [['value' => '0a4b1a61-1e87-4cb9-9a41-1080fd8af949']],
      'type' => [['target_id' => 'weird']],
      'langcode' => [['value' => 'hu']],
      'status' => [['value' => 1]],
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179686]],
      'reusable' => [['value' => 1]],
      'default_langcode' => [['value' => 1]],
      'revision_created' => [['value' => 1612179311]],
      'revision_user' => [['target_id' => 1]],
      'revision_log' => [
        [
          'value' => 'Updated EN Bean translation by admin, marked as up-to-date.',
        ],
      ],
      'revision_default' => [['value' => 1]],
      'revision_translation_affected' => [],
      'content_translation_source' => [['value' => 'und']],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1612179332]],
      'title' => [['value' => 'Bean #8 title']],
      'field_string_translatable' => [
        [
          'value' => 'New Bean #8 hu string (second, last HU version)',
        ],
      ],
    ], $this->getImportantEntityProperties($default_block_revision));

    $this->assertCount(2, $default_block_revision->getTranslationLanguages(FALSE));

    // Icelandic still has to be outdated.
    $default_revision_icelandic = [
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179686]],
      'revision_log' => [
        [
          'value' => 'Updated EN Bean translation by admin, marked as up-to-date.',
        ],
      ],
      'revision_translation_affected' => [],
      'content_translation_outdated' => [['value' => 1]],
    ] + $fourth_revision_icelandic;
    $this->assertEquals($default_revision_icelandic, $this->getImportantEntityProperties($default_block_revision->getTranslation('is')));
    // English should be outdated.
    $default_revision_english = [
      'info' => [['value' => 'Bean #8 (new HU default)']],
      'changed' => [['value' => 1612179686]],
      'revision_log' => [
        [
          'value' => 'Updated EN Bean translation by admin, marked as up-to-date.',
        ],
      ],
      'content_translation_outdated' => [['value' => 0]],
      'content_translation_uid' => [['target_id' => 1]],
      'content_translation_created' => [['value' => 1612179686]],
      'field_string_translatable' => [
        [
          'value' => 'Updated Bean #8 IS string (second EN translation)',
        ],
      ],
    ] + $fourth_revision_english;
    $this->assertEquals($default_revision_english, $this->getImportantEntityProperties($default_block_revision->getTranslation('en')));
  }

  /**
   * Whether there were bean entity UUIDs to migrate over.
   *
   * @return bool
   *   Whether there were bean entity UUIDs to migrate over.
   */
  protected function beanUuidsShouldBeMigrated(): bool {
    $bean_uuid_installed_in_source = $this->sourceDatabase->select('system', 's')
      ->fields('s', ['status'])
      ->condition('s.name', 'bean_uuid')
      ->execute()
      ->fetchField();

    return $bean_uuid_installed_in_source;
  }

}
