<?php

namespace Drupal\bean_migrate\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Exception\RequirementsException;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Migration source plugin for Bean translation settings.
 *
 * @MigrateSource(
 *   id = "bean_translation_settings",
 *   source_module = "bean"
 * )
 */
class BeanTranslationSettings extends BeanType {

  /**
   * Whether content translation is installed on the destination site.
   *
   * @var bool
   */
  protected $contentTranslationInstalled;

  /**
   * Constructs a BeanTranslationSettings instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\migrate\Plugin\MigrationInterface $migration
   *   The current migration.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param bool $content_translation_is_installed
   *   Whether Content Translation is installed on the destination site.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, bool $content_translation_is_installed) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);

    $this->contentTranslationInstalled = $content_translation_is_installed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')->moduleExists('content_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    // A field based subquery for finding out which bean types have at least one
    // translatable field. This will mean that content translation should be
    // enabled for those types.
    // We also want to include the "field_title" field provided by the title
    // module, but otherwise this query is basically equal to the field instance
    // source plugin's query.
    // @see \Drupal\field\Plugin\migrate\source\d7\FieldInstance::query()
    $beans_with_translatable_fields_query = $this->select('field_config_instance', 'fci')
      ->fields('fci', ['bundle'])
      ->condition('fc.active', 1)
      ->condition('fc.storage_active', 1)
      ->condition('fc.deleted', 0)
      ->condition('fci.deleted', 0)
      ->condition('fci.entity_type', 'bean');
    $beans_with_translatable_fields_query->join('field_config', 'fc', 'fci.field_id = fc.id');
    $beans_with_translatable_fields_query->addExpression('MAX(fc.translatable)', 'translatable');
    $beans_with_translatable_fields_query->groupBy('fci.bundle');

    // Left join the translatable field query.
    $query->leftJoin($beans_with_translatable_fields_query, 'btrf', 'btrf.bundle = bean.type');
    // Add the corresponding entity translation settings form the variable
    // table.
    $query->leftJoin('variable', 'v_ets', "v_ets.name = CONCAT('entity_translation_settings_bean__', bean.type)");
    // Add the field bundle settings form the variable table (again).
    $query->leftJoin('variable', 'v_fbs', "v_fbs.name = CONCAT('field_bundle_settings_bean__', bean.type)");

    // Add the subquery's "translatable" value.
    $query->addField('btrf', 'translatable', 'content_translation_enabled');
    // Add the serialized entity translation settings.
    $query->addField('v_ets', 'value', 'et_settings_serialized');
    // Add the serialized field bundle settings.
    $query->addField('v_fbs', 'value', 'fb_settings_serialized');

    $query->groupBy('bean.type')
      ->groupBy('btrf.translatable')
      ->groupBy('v_ets.value')
      ->groupBy('v_fbs.value');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeIterator() {
    $rows = [];
    foreach (parent::initializeIterator() as $item) {
      $et_settings_serialized = !empty($item['et_settings_serialized']) && is_string($item['et_settings_serialized'])
        ? $item['et_settings_serialized']
        : serialize([]);
      $settings = unserialize($et_settings_serialized);
      // For defaults, see entity_translation_settings() in the Entity
      // Translation module.
      $settings += [
        'hide_language_selector' => TRUE,
        'exclude_language_none' => FALSE,
        'shared_fields_original_only' => FALSE,
      ];

      $fb_settings_serialized = !empty($item['fb_settings_serialized']) && is_string($item['fb_settings_serialized'])
        ? $item['fb_settings_serialized']
        : serialize([]);
      $field_bundle_settings = unserialize($fb_settings_serialized);
      // Default weight is "5", see entity_translation_field_extra_fields() in
      // the Entity Translation module.
      $language_weight = $field_bundle_settings['extra_fields']['form']['language']['weight'] ?? 5;

      $item += [
        // In Drupal 9, the equivalent "lock_language" behaviour is always TRUE
        // and isn't configurable.
        'language_alterable' => $settings['hide_language_selector'] ? 0 : 1,
        /* Current interface language "current_interface" does not have a
         * constant.
         * @see language_get_default_langcode() */
        'default_langcode' => $settings['default_language'] ?? 'current_interface',
        'untranslatable_fields_hide' => (int) $settings['shared_fields_original_only'],
        // In Drupal 9, the "exclude_language_none" configuration belongs to the
        // "langcode" entity form element's "include_locked" setting.
        'langcode_include_locked' => $settings['exclude_language_none'] ? 0 : 1,
        'langcode_weight' => (int) $language_weight,
      ];

      // If Content Translation isn't installed on the destination, then the
      // content_translation related third party settings would cause schema
      // errors.
      if (!$this->contentTranslationInstalled) {
        unset($item['content_translation_enabled']);
        unset($item['untranslatable_fields_hide']);
      }

      $rows[] = $item;
    }
    return new \ArrayIterator($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return parent::fields() + [
      'content_translation_enabled' => $this->t('Whether content translation was enabled for the bean type.'),
      'language_alterable' => $this->t('Whether the language selector showed up on the bean add form.'),
      'default_langcode' => $this->t('The default language core configured for the bean type.'),
      'untranslatable_fields_hide' => $this->t('Whether the untranslatable fields are hidden on the translation edit form.'),
      'langcode_include_locked' => $this->t('Whether the language selector includes language none ("und").'),
      'langcode_weight' => $this->t('The weight of the language selector widget on the entity form.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    parent::checkRequirements();

    if (!$this->moduleExists('entity_translation')) {
      throw new RequirementsException('The Entity Translation module is not enabled in the source site.', [
        'source_module' => 'entity_translation',
      ]);
    }
  }

}
