<?php

namespace Drupal\xhprof\XHProfLib;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\xhprof\XHProfLib\Runs\FileRuns;

class XHProf {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @param ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Conditionally enable XHProf profiling.
   */
  public function enable() {
    // @todo: consider a variable per-flag instead.
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
  }

  /**
   * Check whether XHProf should be enabled for the current request.
   */
  function isEnabled() {
    $enabled = FALSE;
    $config = $this->configFactory->get('xhprof.config');

    if (extension_loaded('xhprof') && $config->get('xhprof_enabled')) {
      $enabled = TRUE;
      if (arg(0) == 'admin' && $config->get('xhprof_disable_admin_paths')) {
        $enabled = FALSE;
      }
      $interval = $config->get('xhprof_interval');
      if ($interval && mt_rand(1, $interval) % $interval != 0) {
        $enabled = FALSE;
      }
    }
    return $enabled;
  }

  /**
   * @param $run_id
   * @param string $type
   *
   * @return string
   */
  function link($run_id, $type = 'link') {
    $url = url(XHPROF_PATH . '/' . $run_id, array(
      'absolute' => TRUE,
    ));
    return $type == 'url' ? $url : l(t('XHProf output'), $url);
  }

  /**
   * @return mixed
   */
  public function shutdown($runId) {
    $namespace = $this->configFactory->get('system.site')->get('name'); // namespace for your application
    $xhprof_data = xhprof_disable();
    //$class = \Drupal::config('xhprof.config')->get('xhprof_default_class');
    $xhprof_runs = new FileRuns();
    return $xhprof_runs->saveRun($xhprof_data, $namespace, $runId);
  }

}
