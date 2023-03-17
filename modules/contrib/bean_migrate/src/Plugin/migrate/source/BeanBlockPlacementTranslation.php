<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Migration source plugin for Bean block placement title translations.
 *
 * @MigrateSource(
 *   id = "bean_block_placement_translation",
 *   source_module = "bean"
 * )
 */
class BeanBlockPlacementTranslation extends BeanBlockPlacement {

  /**
   * Translation type and callback map.
   *
   * @const string[]
   */
  const TRANSLATION_TYPE_MAP = [
    'i18n' => [
      'join_callback' => 'i18nJoin',
      'requirements_callback' => 'i18nTranslationRequirements',
    ],
    'entity_translation' => [
      'join_callback' => 'entityTranslationJoin',
      'requirements_callback' => 'entityTranslationRequirements',
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    $configuration += [
      'translation_type' => NULL,
      'type' => NULL,
    ];

    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);

    $this->blockRoleTable = 'block_role';
    $this->userRoleTable = 'role';
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Get the potential block translation languages.
    $languages_query = $this->select('languages', 'l')
      ->fields('l', ['language'])
      ->condition('l.enabled', 1)
      ->condition('l.language', $this->defaultLanguage, '<>');

    $unionquery = NULL;
    foreach ($languages_query->execute()->fetchAllKeyed(0, 0) as $langcode) {
      $blockalias = "block_$langcode";
      $beanalias = "bean_$langcode";
      $union_subquery = $this->select('block', $blockalias)
        ->fields($blockalias, [
          'module',
          'delta',
          'theme',
          'i18n_mode',
          'title',
          'status',
        ])
        ->fields($beanalias, ['type']);
      $union_subquery->join('bean', $beanalias, "$blockalias.delta = $beanalias.delta");
      $union_subquery->addField($blockalias, 'bid', 'block_id');
      $union_subquery->addExpression(":{$blockalias}_langcode", 'langcode', [
        ":{$blockalias}_langcode" => $langcode,
      ]);

      if (!$unionquery instanceof SelectInterface) {
        $unionquery = $union_subquery;
        continue;
      }

      $unionquery->union($union_subquery, 'ALL');
    }

    $query = $this->select($unionquery, 'b')
      ->fields('b')
      ->condition('b.status', 1)
      ->condition('b.module', 'bean')
      ->orderBy('b.block_id', 'ASC')
      ->orderBy('b.langcode', 'ASC');

    if ($type = $this->configuration['type']) {
      $query->condition('b.type', $type);
    }

    $translation_type = $this->configuration['translation_type'];
    $join_callback = self::TRANSLATION_TYPE_MAP[$translation_type]['join_callback'];
    call_user_func_array([$this, $join_callback], [$query]);

    return $query;
  }

  /**
   * Adds i18n block title translations to the main query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The main query.
   */
  protected function i18nJoin(SelectInterface $query): void {
    $tables = $query->getTables();
    $main_query_alias = reset($tables)['alias'];
    $i18n_query = $this->select('locales_target', 'lt')
      ->fields('i18ns', ['objectid'])
      ->fields('lt', ['translation', 'language'])
      ->condition('i18ns.type', 'bean')
      ->condition('i18ns.property', 'title');
    $i18n_query->join('i18n_string', 'i18ns', 'lt.lid = i18ns.lid');
    $query->leftJoin(
      $i18n_query,
      'i18n',
      // Block titles with "<none>" value are actually translatable.
      "i18n.objectid = $main_query_alias.delta AND i18n.language = $main_query_alias.langcode AND $main_query_alias.i18n_mode = 1 AND $main_query_alias.title <> ''"
    );
    $query->fields('i18n', ['language', 'translation']);
    $query->isNotNull('i18n.language');
  }

  /**
   * Adds entity translation block title translations to the main query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The main query.
   */
  protected function entityTranslationJoin(SelectInterface $query): void {
    $tables = $query->getTables();
    $main_query_alias = reset($tables)['alias'];
    // If there are bean entity translations, and the title property is replaced
    // by the "title" module, then the non-translated bean block placement's
    // title is the value of the "title_field" in the site's default language.
    $et_translation_query = $this->select('field_data_title_field', 'tft')
      ->condition('tft.entity_type', 'bean')
      ->fields('tftbean', ['delta'])
      ->fields('tft', ['language']);
    $et_translation_query->addField('tft', 'title_field_value', 'translation');
    $et_translation_query->innerJoin('bean', 'tftbean', 'tft.entity_id = tftbean.bid');
    // The value of the "i18n_mode" column can be ignored.
    $query->leftJoin(
      $et_translation_query,
      'et',
      "et.delta = $main_query_alias.delta AND et.language = $main_query_alias.langcode AND $main_query_alias.title IN (:title_values[])",
      [
        ':title_values[]' => ['', '<none>'],
      ]
    );
    $query->fields('et');
    $query->isNotNull('et.language');
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return parent::getIds() + [
      'langcode' => [
        'type' => 'string',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'block_id' => $this->t('The block numeric identifier.'),
      'module' => $this->t('The module providing the block.'),
      'delta' => $this->t("The block's delta."),
      'theme' => $this->t('Which theme the block is placed in.'),
      'status' => $this->t('Whether or not the block is enabled.'),
      'weight' => $this->t('Weight of the block for ordering within regions.'),
      'region' => $this->t('Region the block is placed in.'),
      'visibility' => $this->t('Visibility expression.'),
      'pages' => $this->t('Pages list.'),
      'title' => $this->t('Block title.'),
      'cache' => $this->t('Cache rule.'),
      'i18n_mode' => $this->t('Internationalization mode.'),
      'type' => $this->t('The type (bundle) of the bean entity what this block config displays.'),
      'langcode' => $this->t('Language code for this title translation.'),
      'translation' => $this->t('The translated block label.'),
      'language' => $this->t('Language code for this title translation.'),
      'default_theme' => $this->t('The default frontend theme.'),
      'admin_theme' => $this->t('The default admin theme, if any.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    $translation_type = $this->configuration['translation_type'];
    if (
      !$translation_type ||
      !is_string($translation_type) ||
      !array_key_exists($translation_type, self::TRANSLATION_TYPE_MAP)
    ) {
      throw new RequirementsException(sprintf("The 'translation_type' configuration is required and its allowed values are '%s'.", implode("', '", array_keys(self::TRANSLATION_TYPE_MAP))));
    }

    call_user_func([
      $this,
      self::TRANSLATION_TYPE_MAP[$translation_type]['requirements_callback'],
    ]);
    parent::checkRequirements();
  }

  /**
   * Checks i18n translation requirements.
   *
   * @throws \Drupal\migrate\Exception\RequirementsException
   *   Thrown when requirements are not met.
   */
  protected function i18nTranslationRequirements(): void {
    if (!$this->moduleExists('i18n_block')) {
      throw new RequirementsException("The 'i18n_block' module isn't enabled on the source site.");
    }
  }

  /**
   * Checks entity translation requirements.
   *
   * @throws \Drupal\migrate\Exception\RequirementsException
   *   Thrown when requirements are not met.
   */
  protected function entityTranslationRequirements(): void {
    if (
      !$this->moduleExists('entity_translation') ||
      !$this->moduleExists('title') ||
      !$this->getDatabase()->schema()->tableExists('field_data_title_field')
    ) {
      throw new RequirementsException("Bean entity title translations cannot be found.");
    }
  }

}
