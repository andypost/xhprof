To get the xhprof_data written after a test is run, you need to
setup php to load the append and prepend scripts.

To make this work in an apache vhost definition, use:

  php_value auto_prepend_file "$PATH_TO_XHPROF_TESTS_MODULE_DIR/xhprof_tests.prepend.php"
  php_value auto_append_file  "$PATH_TO_XHPROF_TESTS_MODULE_DIR/xhprof_tests.append.php"

