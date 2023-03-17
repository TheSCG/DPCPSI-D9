<?php

namespace Drupal\paragraphs\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\paragraphs\Plugin\migrate\source\d7\FieldCollectionItem;
use Drupal\paragraphs\Plugin\migrate\field\FieldCollection;

/**
 * Provides a 'FieldCollectionItemContentTranslation' migrate source.
 *
 * @MigrateSource(
 *  id = "d7_field_collection_item_content_translation_translations",
 *  source_module = "field_collection"
 * )
 */
class FieldCollectionItemContentTranslationTranslations extends FieldCollectionItem {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    // Join to node & content translation tables to get parent entity languages.
    // Also add the field_collection_item source item so we can map to a single
    // entity in the destination and finally the source langcode.
    $query->innerJoin('node', 'n', 'fc.entity_id = n.nid and n.nid <> n.tnid');
    $query->innerJoin('node', 'sn', 'sn.nid = n.tnid');
    $query->innerJoin('field_data_' . $this->configuration['field_name'], 'fcs', 'fcs.entity_id = n.tnid');
    $query->innerJoin('field_collection_item', 'fis', 'fcs.' . $this->configuration['field_name'] . '_value = fis.item_id');
    $query->condition('fc.entity_type', 'node');
    $query->condition('fcs.entity_type', 'node');
    $query->addField('n', 'language');
    $query->addField('fis', 'item_id', 'source_item_id');
    $query->addField('sn', 'language', 'source_langcode');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Add source langcode and source item id.
    $bundle = $row->getSourceProperty('field_name');
    $bundle = substr($bundle, FieldCollection::FIELD_COLLECTION_PREFIX_LENGTH);
    $row->setSourceProperty('bundle', $bundle);
    $source_item_id = $row->getSourceProperty('source_item_id');
    $row->setSourceProperty('source_item_id', $source_item_id);
    $source_langcode = $row->getSourceProperty('source_langcode');
    $row->setSourceProperty('source_langcode', $source_langcode);

    // Get Field API field values.
    $field_names = array_keys($this->getFields('field_collection_item', $row->getSourceProperty('field_name')));
    $item_id = $row->getSourceProperty('item_id');
    $revision_id = $row->getSourceProperty('revision_id');

    foreach ($field_names as $field_name) {
      // We specifically do not use the language to the load field values.
      // It's assumed if the field collections are attached to content
      // translation nodes then entity translation isn't enabled on the
      // field collections themselves.
      $value = $this->getFieldValues('field_collection_item', $field_name, $item_id, $revision_id, NULL);
      $row->setSourceProperty($field_name, $value);
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = parent::fields();
    $fields['language'] = $this->t('The language of the referenced node.');
    $fields['source_item_id'] = $this->t('The source translation item id.');
    $fields['source_langcode'] = $this->t('The source translation language.');

    return $fields;
  }

}
