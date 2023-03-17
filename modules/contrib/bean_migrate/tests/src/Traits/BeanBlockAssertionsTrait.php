<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\block\Entity\Block;
use Drupal\language\ConfigurableLanguageManager;

/**
 * Trait for bean block related assertions.
 */
trait BeanBlockAssertionsTrait {

  /**
   * List of block config properties whose value shouldn't have to be checked.
   *
   * @var string[]
   */
  protected $blockUnconcernedProperties = [
    'uuid',
    '_core',
  ];

  /**
   * Checks the block config entity which was migrated from BLOCK #1.
   *
   * This checks a block that WASN'T A BEAN in Drupal 7.
   */
  public function assertBlock1Block() {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_block_1');

    $this->assertInstanceOf(Block::class, $block);

    // We need to know the uuid of the block content entity this plugin instance
    // display.
    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Custom block #1']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:basic:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_block_1',
      'theme' => 'bartik',
      'region' => 'featured_bottom_third',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => 'Title of Custom block #1',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));
  }

  /**
   * Checks the block config entity which was migrated from Bean #3.
   */
  public function assertBean3Block() {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_bean_yellow_image');
    $this->assertInstanceOf(Block::class, $block);

    // We need to know the uuid of the block content entity this plugin instance
    // display.
    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #3']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:image:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_bean_yellow_image',
      'theme' => 'bartik',
      'region' => 'featured_bottom_first',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => 'Yellow image',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));
  }

  /**
   * Checks the block config entity which was migrated from Bean #4.
   */
  public function assertBean4Block(string $type = 'simple') {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_bean_bean_4_rev_1');
    $this->assertInstanceOf(Block::class, $block);

    // We need to know the uuid of the block content entity this plugin instance
    // display.
    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin label of Bean #4']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:{$type}:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_bean_bean_4_rev_1',
      'theme' => 'bartik',
      'region' => 'featured_bottom_second',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => 'Bean #4 (rev 1)',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));
  }

  /**
   * Check translated block placement migration of Bean #5 (entity_translation).
   */
  public function assertBean5Block() {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_bean_admin_fully_translatable_bea');
    $this->assertInstanceOf(Block::class, $block);

    // We need to know the uuid of the block content entity this plugin instance
    // display.
    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Fully translatable Bean #5']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:fully_translatable:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_bean_admin_fully_translatable_bea',
      'theme' => 'bartik',
      'region' => 'footer_first',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => '[IS (default)] Bean #5 Title - new published revision',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));

    $language_manager = $this->container->get('language_manager');
    assert($language_manager instanceof ConfigurableLanguageManager);
    $config = $language_manager->getLanguageConfigOverride('en', 'block.block.' . $block->id());
    $this->assertEquals([
      'settings' => [
        'label' => '[EN] Bean #5 Title',
      ],
    ], $config->get());
  }

  /**
   * Checks block placement migration of Bean #6 (no title, no i18n_block).
   */
  public function assertBean6Block() {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_bean_weird_bean_6');
    $this->assertInstanceOf(Block::class, $block);

    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Weird Bean #6']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:weird:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_bean_weird_bean_6',
      'theme' => 'bartik',
      'region' => 'footer_second',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => 'Bean #6 Title',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));
  }

  /**
   * Checks translated bean block placement migration of Bean #7 (i18n_block).
   */
  public function assertBean7Block() {
    $block = \Drupal::entityTypeManager()
      ->getStorage('block')
      ->load('bartik_bean_weird_bean_7');
    $this->assertInstanceOf(Block::class, $block);

    $block_contents = \Drupal::entityTypeManager()
      ->getStorage('block_content')
      ->loadByProperties(['info' => 'Admin – Weird Bean #7']);
    $block_content = reset($block_contents);

    $this->assertEquals([
      'langcode' => $this->expectedDefaultLanguageCode,
      'status' => TRUE,
      'dependencies' => [
        'content' => ["block_content:weird:{$block_content->uuid()}"],
        'module' => ['block_content'],
        'theme' => ['bartik'],
      ],
      'id' => 'bartik_bean_weird_bean_7',
      'theme' => 'bartik',
      'region' => 'footer_third',
      'weight' => 0,
      'provider' => NULL,
      'plugin' => "block_content:{$block_content->uuid()}",
      'settings' => [
        'id' => "block_content:{$block_content->uuid()}",
        'label' => '[IS] Block placement title override for Weird Bean #7',
        'provider' => 'block_content',
        'label_display' => 'visible',
        'status' => TRUE,
        'info' => '',
        'view_mode' => 'full',
      ],
      'visibility' => [],
    ], $this->getImportantEntityProperties($block));

    $language_manager = $this->container->get('language_manager');
    assert($language_manager instanceof ConfigurableLanguageManager);
    $config = $language_manager->getLanguageConfigOverride('hu', 'block.block.' . $block->id());
    $this->assertEquals([
      'settings' => [
        'label' => '[HU] Block placement title override for Weird Bean #7',
      ],
    ], $config->get());

    $config = $language_manager->getLanguageConfigOverride('en', 'block.block.' . $block->id());
    $this->assertEquals([
      'settings' => [
        'label' => '[EN] Block placement title override for Weird Bean #7',
      ],
    ], $config->get());
  }

}
