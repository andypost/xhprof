<?php

/**
 * Test case for profiling Drupal child instances.
 *
 * Use this test case when you want to profile http requests or sections of 
 * code.
 */
class DrupalPerformanceTestCase extends DrupalWebTestCase {

  /**
   * Profile a http request.
   *
   * @param $url
   *   As you would pass to drupalGet().
   * @param $setup_type
   *   Unique key for this type of setup. This is used so that it is possible
   *   to meaningfully compare the data collected from this request with other
   *   runs of the same type.
   */
  public function drupalProfileUrl($url, $setup_type) {
    $options['query'] = array(
      'simpletest_test_id' => $this->testId,
      'xhprof_tests_do_run' => "$url:$setup_type",
    );
    $this->drupalGet($url, $options);
  }

  /**
   * Profile a function against a known setup.
   * 
   * @param $function
   *   The name of the function to profile.
   * @param $setup_type
   *   Unique key for this type of setup. This is used so that it is possible
   *   to meaningfully compare the data collected from this request with other
   *   runs of the same type.
   * @param array $args 
   *   Arguments to pass to the function.
   * @return void
   */
  public function drupalProfileFunction($function, $setup_type, $args = array()) {
    // Intentionally ugly - we want to the overhead of call_user_func_array().
    switch (count($args)) {
      case 0:
        xhprof_enable();
        $function();
        $run_data = xhprof_disable();
        break;
      case 1:
        xhprof_enable();
        $function($args[0]);
        $run_data = xhprof_disable();
        break;
      case 2:
        xhprof_enable();
        $function($args[0], $args[1]);
        $run_data = xhprof_disable();
        break;
      case 3:
        xhprof_enable();
        $function($args[0], $args[1], $args[2]);
        $run_data = xhprof_disable();
        break;
      case 4:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3]);
        $run_data = xhprof_disable();
        break;
      case 5:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4]);
        $run_data = xhprof_disable();
        break;
      case 6:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
        $run_data = xhprof_disable();
        break;
      case 7:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
        $run_data = xhprof_disable();
        break;
      case 8:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
        $run_data = xhprof_disable();
        break;
      case 9:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
        $run_data = xhprof_disable();
        break;
      case 10:
        xhprof_enable();
        $function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]);
        $run_data = xhprof_disable();
        break;
    }
    if (isset($run_data)) {
      xhprof_tests_save_run_data("$setup_type:$function", $this->testId, $run_data);
    }
  }
}

