<?php

if (isset($_GET['xhprof_tests_do_run']) && ($test_prefix = drupal_valid_test_ua())) {
  $run_data = xhprof_disable();

  // When run in a child Drupal, the database connection has a test prefix, so
  // we remove that and reset the default connection.
  $connection_info = Database::getConnectionInfo('default');
  Database::renameConnection('default', 'xhprof_tests_default');
  foreach ($connection_info as $target => $value) {
    $connection_info[$target]['prefix'] = array(
      'default' => str_replace($test_prefix, '', $value['prefix']['default']),
    );
  }
  Database::addConnectionInfo('default', 'default', $connection_info['default']);

  // xhprof_tests module is probably not enabled in the child Drupal, so we need
  // to take care to load the code ourselves.
  require_once DRUPAL_ROOT . '/' . drupal_get_path('module', 'xhprof_tests') . '/xhprof_tests.module';
  xhprof_tests_save_run_data($_GET['xhprof_tests_do_run'], $_GET['simpletest_test_id'], $run_data);
}

