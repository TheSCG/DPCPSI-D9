<?php

function wide_migration_migrations_plugins_alter{
  foreach ($migrations as $migrationId => &$migration) {
  if ($migrationId == 'upgrade_d7_filter_format') {
    array_unshift($migration['process']['filters'], [
      'plugin' => 'skip_on_empty',
      'method' => 'row',
    ]);
  }
}
}
