<?php

namespace Drupal\paragraphs\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\paragraphs\Plugin\migrate\source\d7\FieldCollectionItem;
use Drupal\paragraphs\Plugin\migrate\field\FieldCollection;

/**
 * Provides a 'FieldCollectionItemContentTranslation' migrate source.
 *
 * @MigrateSource(
 *  id = "d7_field_collection_item_content_translation_source",
 *  source_module = "field_collection"
 * )
 */
class FieldCollectionItemContentTranslationSource extends FieldCollectionItem {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    // Join to node & content translation tables to get parent entity langauges.
    // Also add the field_collection_item source item so we can map to a single
    // entity in the destination.
    $query->innerJoin('node', 'n', 'fc.entity_id = n.nid and n.nid = n.tnid');
    $query->addField('n', 'language');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Add language field.
    $bundle = $row->getSourceProperty('field_name');
    $bundle = substr($bundle, FieldCollection::FIELD_COLLECTION_PREFIX_LENGTH);
    $row->setSourceProperty('bundle', $bundle);

    // Get Field API field values.
    $field_names = array_keys($this->getFields('field_collection_item', $row->getSourceProperty('field_name')));
    $item_id = $row->getSourceProperty('item_id');
    $revision_id = $row->getSourceProperty('revision_id');
    $language = $row->getSourceProperty('language');

    foreach ($field_names as $field_name) {
      $field_language = $field['translatable'] ? $language : NULL;
      $value = $this->getFieldValues('field_collection_item', $field_name, $item_id, $revision_id, $field_language);
      $row->setSourceProperty($field_name, $value);
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    $fields['language'] = $this->t('The language of the parent entity.');

    return $fields;
  }

}
