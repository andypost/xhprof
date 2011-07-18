<?php

if (isset($_GET['xhprof_tests_do_run'])) {
  xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

