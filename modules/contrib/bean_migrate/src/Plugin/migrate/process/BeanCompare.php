<?php

namespace Drupal\bean_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Migrate process plugin for comparing two values.
 *
 * Example:
 * @code
 * destination_property:
 *   plugin: bean_compare
 *   source:
 *     - property_1
 *     - property_2
 *   operator: '<='
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "bean_compare"
 * )
 */
class BeanCompare extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    [$variable_1, $variable_2] = (array) $value;
    $operator = $this->configuration['operator'] ?? '===';

    switch ($operator) {
      case '==':
        return $variable_1 == $variable_2;

      case '===':
        return $variable_1 === $variable_2;

      case '!=':
        return $variable_1 != $variable_2;

      case '!==':
        return $variable_1 !== $variable_2;

      case '<>':
        return $variable_1 <> $variable_2;

      case '<':
        return $variable_1 < $variable_2;

      case '<=':
        return $variable_1 <= $variable_2;

      case '>':
        return $variable_1 > $variable_2;

      case '>=':
        return $variable_1 >= $variable_2;
    }

    throw new MigrateException(sprintf("Operator '%s' is not supported", $operator));

  }

}
