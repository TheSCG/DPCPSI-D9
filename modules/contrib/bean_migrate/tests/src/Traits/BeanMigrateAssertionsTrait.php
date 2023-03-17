<?php

namespace Drupal\Tests\bean_migrate\Traits;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Trait for the migration related assertions used in Bean Migrate tests.
 */
trait BeanMigrateAssertionsTrait {

  use BeanBlockAssertionsTrait;
  use BeanContentAssertionsTrait;
  use BeanFieldConfigAssertionsTrait;
  use BeanFormDisplayAssertionsTrait;
  use BeanTranslationSettingsAssertionsTrait;
  use BeanTypeAssertionsTrait;
  use BeanViewDisplayAssertionsTrait;

  /**
   * Performs the relevant assertions.
   *
   * @param string $type
   *   The ID of the expected block content type migrated from the "simple" bean
   *   type.
   */
  protected function performBeanMigrationAssertions(string $type = 'simple') {
    // Ensure that the expected block content types are present.
    $this->assertBeanSimpleBlockContentType($type);
    $this->assertBeanImageBlockContentType();

    if ($this->isMultilingualTest) {
      // Test the language content settings.
      $this->assertBeanSimpleTranslationSettings($type);
      $this->assertBeanImageTranslationSettings();
      $this->assertBeanFullyTranslatableTranslationSettings();
      $this->assertBeanWeirdTranslationSettings();
    }

    // Verify that the expected field instance configurations were migrated.
    $this->assertNodeBodyFieldInstance();
    $this->assertBeanImageTitleFieldInstance();
    $this->assertBeanImageImageFieldInstance();
    $this->assertBeanSimpleBodyFieldInstance($type);
    $this->assertBeanSimpleTitleFieldInstance($type);

    if ($this->isMultilingualTest) {
      $this->assertBeanFullyTranslatableBodyFieldInstance();
      $this->assertBeanFullyTranslatableTitleFieldInstance();
      $this->assertBeanFullyTranslatableStringFieldInstance();
      $this->assertBeanWeirdStringFieldInstance();
      $this->assertBeanWeirdTitleFieldInstance();
    }

    // Check the actual block content entities.
    $this->assertBean1($type);
    $this->assertBean2($type);
    $this->assertBean3();
    $this->assertBean4($type);
    if ($this->isMultilingualTest) {
      $this->assertBean5();
      $this->assertBean6();
      $this->assertBean7();
      $this->assertBean8();
    }

    // Check the block configurations (block placements).
    $this->assertBlock1Block();
    $this->assertBean3Block();
    $this->assertBean4Block($type);
    if ($this->isMultilingualTest) {
      $this->assertBean5Block();
      $this->assertbean6block();
      $this->assertBean7Block();
    }

    // Check the form display.
    $this->assertBeanSimpleFormDisplay($type);
    $this->assertBeanImageFormDisplay();
    if ($this->isMultilingualTest) {
      $this->assertBeanFullyTranslatableFormDisplay();
      $this->assertBeanWeirdFormDisplay();
    }

    // Check the view displays.
    $this->assertBeanSimpleViewDisplays($type);
    $this->assertBeanImageViewDisplays();
    if ($this->isMultilingualTest) {
      $this->assertBeanFullyTranslatableViewDisplay();
      $this->assertBeanWeirdViewDisplay();
    }
  }

  /**
   * Filters out unconcerned properties from an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity instance.
   *
   * @return array
   *   The important entity property values as array.
   */
  protected function getImportantEntityProperties(EntityInterface $entity) {
    $entity_type_id = $entity->getEntityTypeId();
    $exploded = explode('_', $entity_type_id);
    $prop_prefix = count($exploded) > 1
      ? $exploded[0] . implode('', array_map('ucfirst', array_slice($exploded, 1)))
      : $entity_type_id;
    $property_filter_preset_property = "{$prop_prefix}UnconcernedProperties";
    $entity_array = $entity->toArray();
    $unconcerned_properties = property_exists(get_class($this), $property_filter_preset_property)
      ? $this->$property_filter_preset_property
      : [
        'uuid',
        'langcode',
        'dependencies',
        '_core',
      ];

    foreach ($unconcerned_properties as $item) {
      unset($entity_array[$item]);
    }

    if ($entity_type_id === 'block_content' && !$this->beanUuidsShouldBeMigrated()) {
      unset($entity_array['uuid']);
    }

    return $entity_array;
  }

  /**
   * Load all non-default revisions of an entity.
   */
  protected function loadNonDefaultEntityRevisions(ContentEntityInterface $entity): array {
    $revisions = [];
    $entity_type = $entity->getEntityType();
    $entity_type_id = $entity_type->id();
    $revision_key = $entity_type->getKey('revision');
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    $revision_ids = $storage->getQuery()
      ->allRevisions()
      ->condition($entity_type->getKey('id'), $entity->id())
      ->sort($revision_key, 'ASC')
      ->execute();
    if (empty($revision_ids)) {
      return $revisions;
    }

    foreach (array_keys($revision_ids) as $revision_id) {
      $entity_revision = $storage->loadRevision($revision_id);
      if ($entity_revision->getRevisionId() === $entity->getRevisionId()) {
        continue;
      }
      $key = implode(':', [$entity_revision->id(), $revision_id]);
      $revisions[$key] = $entity_revision;
    }

    return $revisions;
  }

}
