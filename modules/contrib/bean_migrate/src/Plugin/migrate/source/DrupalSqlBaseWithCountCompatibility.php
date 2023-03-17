<?php

/**
 * @file
 * Base class for working around SQL plugin count compatibility issues.
 *
 * Drupal core 9.1 is the oldest minor which got the fix - but every release
 * prior to 9.1.9 does not have cacheable source plugin counts. Core 9.2
 * alpha2 was the first release with the fix. But only core 9.3.x has a complete
 * fix.
 *
 * @see https://drupal.org/i/3190815
 * @todo Remove after Drupal core 8.x, 9.0.x, 9.1.x and 9.2.x are unsupported.
 */

namespace Drupal\bean_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

if (
  (
    version_compare(\Drupal::VERSION, '9.1.9', 'ge') &&
    version_compare(\Drupal::VERSION, '9.2', 'lt')
  ) ||
  version_compare(\Drupal::VERSION, '9.2.0-alpha2', 'ge')
) {
  /**
   * Class for core where sql source plugin count caching is supported.
   */
  abstract class DrupalSqlBaseWithCountCompatibility extends DrupalSqlBase {

    /**
     * {@inheritdoc}
     */
    protected function doCount() {
      return iterator_count($this->initializeIterator());
    }

  }
}
else {
  /**
   * Class for core where sql source plugin count caching isn't supported.
   */
  abstract class DrupalSqlBaseWithCountCompatibility extends DrupalSqlBase {

    /**
     * {@inheritdoc}
     */
    public function count($refresh = FALSE) {
      return SourcePluginBase::count($refresh);
    }

    /**
     * {@inheritdoc}
     */
    protected function doCount() {
      return SourcePluginBase::doCount();
    }

  }
}
