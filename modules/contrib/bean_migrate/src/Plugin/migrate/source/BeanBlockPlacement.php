<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

use Drupal\block\Plugin\migrate\source\Block;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Migration source plugin for Bean block placements.
 *
 * @MigrateSource(
 *   id = "bean_block_placement",
 *   source_module = "bean"
 * )
 */
class BeanBlockPlacement extends Block {

  /**
   * The source site's default language.
   *
   * @var string
   */
  protected $defaultLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $default_language_default = (object) ['language' => 'en'];
    $this->defaultLanguage = $this->variableGet('language_default', $default_language_default)->language;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('b.module', 'bean');
    $query->condition('b.status', 1);
    $query->join('bean', 'bean', 'b.delta = bean.delta');
    $query->addField('bean', 'bid', 'bean_id');
    $query->addField('bean', 'vid', 'bean_revision_id');
    $query->addField('bean', 'title', 'bean_title');
    $query->addField('bean', 'type');

    if ($type = $this->configuration['type'] ?? NULL) {
      $query->condition('bean.type', $type);
    }

    $query->orderBy('b.bid', 'ASC');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // If there are bean entity translations, and the title property is replaced
    // by the "title" module, then the non-translated bean block placement's
    // title is the value of the "title_field" in the site's default language.
    if (
      $this->moduleExists('entity_translation') &&
      $this->moduleExists('title') &&
      $this->getDatabase()->schema()->tableExists('field_data_title_field')
    ) {
      $bean_title_default = $this->select('field_data_title_field', 'btd')
        ->fields('btd', ['title_field_value'])
        ->condition('btd.entity_type', 'bean')
        ->condition('btd.entity_id', $row->getSourceProperty('bean_id'))
        ->condition('btd.language', $this->defaultLanguage)
        ->execute()
        ->fetchField();

      if ($bean_title_default) {
        $row->setSourceProperty('bean_title_default', $bean_title_default);
      }
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = parent::getIds();
    $ids['delta']['alias'] = 'b';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() + [
      'bean_id' => $this->t('The ID of the bean entity what this block config displays.'),
      'bean_revision_id' => $this->t('The revision ID of the bean entity what this block config displays.'),
      'type' => $this->t('The type (bundle) of the bean entity what this block config displays.'),
      'bean_title' => $this->t('The title of the bean entity. If the block title is empty, then this is the title of the block.'),
      'bean_title_default' => $this->t("If the bean entity displayed by this block is translatable, the this is the block title with in the site's default language."),
    ];
  }

}
