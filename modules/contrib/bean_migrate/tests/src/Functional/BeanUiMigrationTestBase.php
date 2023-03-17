<?php

namespace Drupal\Tests\bean_migrate\Functional;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Tests\bean_migrate\Traits\BeanMigrateAssertionsTrait;
use Drupal\Tests\migrate_drupal_ui\Functional\MigrateUpgradeTestBase;

/**
 * Base class for Bean Migrate functional tests with Drupal's migration UI.
 */
abstract class BeanUiMigrationTestBase extends MigrateUpgradeTestBase {

  use BeanMigrateAssertionsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'bartik';

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
   */
  protected static $modules = [
    'bean_migrate',
    'migrate_drupal_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->loadFixture($this->getDatabaseFixtureFilePath());

    if ($this->isMultilingualTest) {
      $module_installer = \Drupal::service('module_installer');
      assert($module_installer instanceof ModuleInstallerInterface);
      $module_installer->install([
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
  }

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
   * {@inheritdoc}
   */
  protected function getSourceBasePath() {
    return drupal_get_path('module', 'bean_migrate') . '/tests/fixtures/files';
  }

  /**
   * Executes all steps of migrations upgrade.
   */
  public function executeMigrationsWithUi() {
    $this->drupalGet('/upgrade');
    $session = $this->assertSession();
    $session->responseContains("Upgrade a site by importing its files and the data from its database into a clean and empty new install of Drupal");

    $this->submitForm([], 'Continue');
    $session->pageTextContains('Provide credentials for the database of the Drupal site you want to upgrade.');
    $session->fieldExists('mysql[host]');

    // Get valid credentials.
    $edits = $this->translatePostValues($this->getCredentials());

    $this->submitForm($edits, 'Review upgrade');

    $this->submitForm([], 'I acknowledge I may lose data. Continue anyway.');
    $session->statusCodeEquals(200);

    // Submit the review form.
    $this->submitForm([], 'Perform upgrade');
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCounts() {
    // Unused.
    return ['nothing' => 0];
  }

  /**
   * {@inheritdoc}
   */
  protected function getAvailablePaths() {
    // Unused.
    return ['nothing' => 'nothing'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getMissingPaths() {
    // Unused.
    return ['nothing' => 'nothing'];
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityCountsIncremental() {
    // Unused.
    return $this->getEntityCounts();
  }

  /**
   * Creates an array of credentials for the Credential form.
   *
   * Before submitting to the Credential form the array must be processed by
   * BrowserTestBase::translatePostValues() before submitting.
   *
   * @return array
   *   An array of values suitable for BrowserTestBase::translatePostValues().
   *
   * @todo Remove this method override when security support ends of Drupal core
   *   versions 8.9.x and 9.0.x.
   * @see https://www.drupal.org/project/drupal/issues/3143719
   */
  protected function getCredentials() {
    if (is_callable('parent::getCredentials')) {
      return parent::getCredentials();
    }
    $connection_options = $this->sourceDatabase->getConnectionOptions();
    $version = $this->getLegacyDrupalVersion($this->sourceDatabase);
    $driver = $connection_options['driver'];
    $connection_options['prefix'] = $connection_options['prefix']['default'];

    // Use the driver connection form to get the correct options out of the
    // database settings. This supports all of the databases we test against.
    $drivers = drupal_get_database_types();
    $form = $drivers[$driver]->getFormOptions($connection_options);
    $connection_options = array_intersect_key($connection_options, $form + $form['advanced_options']);
    $edit = [
      $driver => $connection_options,
      'source_private_file_path' => $this->getSourceBasePath(),
      'version' => $version,
    ];
    if ($version == 6) {
      $edit['d6_source_base_path'] = $this->getSourceBasePath();
    }
    else {
      $edit['source_base_path'] = $this->getSourceBasePath();
    }
    if (count($drivers) !== 1) {
      $edit['driver'] = $driver;
    }
    return $edit;
  }

}
