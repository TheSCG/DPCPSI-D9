<?php

namespace Drupal\Tests\bean_migrate\Kernel;

use Drupal\Tests\bean_migrate\Traits\BeanMigrateAssertionsTrait;
use Drupal\Tests\migmag\Traits\MigMagKernelTestDxTrait;
use Drupal\Tests\migrate_drupal\Kernel\MigrateDrupalTestBase;

/**
 * Base class for Bean Migrate kernel tests.
 */
abstract class BeanMigrateTestBase extends MigrateDrupalTestBase {

  use BeanMigrateAssertionsTrait;
  use MigMagKernelTestDxTrait;

  /**
   * Multilingual migration test.
   *
   * @var bool
   */
  protected $isMultilingualTest = FALSE;

  /**
   * Expected default language code (on config entities).
   *
   * @var string
   */
  protected $expectedDefaultLanguageCode;

  /**
   * {@inheritdoc}
   *
   * @todo This should be changed to "protected" after Drupal core 8.x security
   *   support ends.
   * @see https://www.drupal.org/node/2909426
   */
  public static $modules = [
    'bean_migrate',
    'block',
    'block_content',
    'field',
    'file',
    'filter',
    'image',
    'migrate',
    'migrate_drupal',
    'node',
    'options',
    'system',
    'text',
    'user',
  ];

  /**
   * Returns the drupal-relative path to the database fixture file.
   *
   * @return string
   *   The path to the database file.
   */
  public function getDatabaseFixtureFilePath() {
    if ($this->isMultilingualTest) {
      return drupal_get_path('module', 'bean_migrate') . '/tests/fixtures/drupal7_bean_multilingual.php';
    }

    return drupal_get_path('module', 'bean_migrate') . '/tests/fixtures/drupal7_bean.php';
  }

  /**
   * Returns the absolute path to the file system fixture directory.
   *
   * @return string
   *   The absolute path to the file system fixture directory.
   */
  public function getFilesystemFixturePath() {
    return implode(DIRECTORY_SEPARATOR, [
      DRUPAL_ROOT,
      drupal_get_path('module', 'bean_migrate'),
      'tests',
      'fixtures',
      'files',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->loadFixture($this->getDatabaseFixtureFilePath());
    $module_handler = \Drupal::moduleHandler();

    if ($module_handler->moduleExists('file')) {
      $this->installEntitySchema('file');
      $this->installSchema('file', 'file_usage');
    }
    if ($module_handler->moduleExists('node')) {
      $this->installEntitySchema('node');
      $this->installSchema('node', 'node_access');
    }
    if ($module_handler->moduleExists('comment')) {
      $this->installEntitySchema('comment');
      $this->installSchema('comment', 'comment_entity_statistics');
    }
    if ($module_handler->moduleExists('block_content')) {
      $this->installEntitySchema('block_content');
    }

    if ($this->isMultilingualTest) {
      $this->enableModules([
        'config_translation',
        'content_translation',
        'language',
      ]);
    }

    // The multilingual Drupal 7 source fixture has Icelandic default
    // language, so the configurations should have been migrated with language
    // "is". The other fixture does not have locale enabled, and the
    // "undefined" default language is changed to "en" by core.
    // @see /core/modules/language/migrations/default_language.yml
    $this->expectedDefaultLanguageCode = $this->isMultilingualTest ? 'is' : 'en';

    // Let's install all "default" configuration.
    $module_list = array_keys($module_handler->getModuleList());
    $this->installConfig($module_list);

    // Install the themes used for this test.
    $this->container->get('theme_installer')->install(['bartik']);

    // Set Bartik as the default public theme.
    $this->config('system.theme')
      ->set('default', 'bartik')
      ->save();
  }

  /**
   * Executes the relevant migrations.
   */
  protected function executeBeanMigrations() {
    // The Drupal 8|9 entity revision migration causes a file not found
    // exception without properly migrated files.
    $fs_fixture_path = $this->getFilesystemFixturePath();
    $file_migration = $this->getMigration('d7_file');
    $source = $file_migration->getSourceConfiguration();
    $source['constants']['source_base_path'] = $fs_fixture_path;
    $file_migration->set('source', $source);

    // Ignore errors of migrations which aren't provided by Bean Migrate.
    $this->startCollectingMessages();
    if ($this->isMultilingualTest) {
      $this->executeMigrations([
        'language',
        'default_language',
      ]);
    }
    $this->executeMigration($file_migration);
    $this->executeMigrations([
      'd7_filter_format',
      'd7_view_modes',
      'd7_field',
      'd7_node_type',
    ]);

    $this->startCollectingMessages();
    $this->executeMigrations([
      'bean_type',
    ]);
    if ($this->isMultilingualTest) {
      $this->executeMigrations([
        'bean_langcode_field_widget',
        'bean_translation_settings',
      ]);
    }
    $this->assertExpectedMigrationMessages();

    $this->executeMigrations([
      'block_content_type',
      'block_content_body_field',
      'd7_field_instance',
      'd7_field_formatter_settings',
      'd7_field_instance_widget_settings',
      'd7_user_role',
      'd7_user',
    ]);

    // Migrate the Bean title related field storage and instance entities.
    $this->startCollectingMessages();
    $this->executeMigrations([
      'bean_title_field',
      'bean_title_field_instance',
      'bean_title_field_formatter',
      'bean_title_field_widget',
    ]);
    $this->assertExpectedMigrationMessages();

    // Migrate the actual content entities.
    $this->startCollectingMessages();
    $this->executeMigrations([
      'bean',
    ]);
    $this->assertExpectedMigrationMessages();

    // Drupal core's Migrate Drupal UI executes d7_custom_block" migrations
    // after bean entity migrations.
    $this->executeMigrations([
      'd7_custom_block',
      'd7_block',
      'd7_node_complete',
    ]);

    // Migrate Bean block placements.
    $this->startCollectingMessages();
    $this->executeMigrations([
      'bean_block',
    ]);
    if ($this->isMultilingualTest) {
      $this->executeMigrations([
        'bean_block_translation_i18n',
        'bean_block_translation_et',
      ]);
    }
    $this->assertExpectedMigrationMessages();
  }

}
