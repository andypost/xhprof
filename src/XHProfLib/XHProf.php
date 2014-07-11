<?php

namespace Drupal\xhprof\XHProfLib;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\xhprof\XHProfLib\Storage\StorageInterface;
use Drupal\xhprof\XHProfLib\Report\ReportEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class XHProf {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @var StorageInterface
   */
  private $storage;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestMatcherInterface
   */
  private $requestMatcher;

  /**
   * @var string
   */
  private $runId;

  /**
   * @var bool
   */
  private $enabled = FALSE;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\xhprof\XHProfLib\Storage\StorageInterface $storage
   * @param \Symfony\Component\HttpFoundation\RequestMatcherInterface $requestMatcher
   */
  public function __construct(ConfigFactoryInterface $configFactory, StorageInterface $storage, RequestMatcherInterface $requestMatcher) {
    $this->configFactory = $configFactory;
    $this->storage = $storage;
    $this->requestMatcher = $requestMatcher;
  }

  /**
   * Conditionally enable XHProf profiling.
   */
  public function enable() {
    // @todo: consider a variable per-flag instead.
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

    $this->enabled = TRUE;
  }

  /**
   * @return array
   */
  public function shutdown($runId) {
    $namespace = $this->configFactory->get('system.site')->get('name');
    $xhprof_data = xhprof_disable();

    $this->enabled = TRUE;

    return $this->storage->saveRun($xhprof_data, $namespace, $runId);
  }

  /**
   * Check whether XHProf is enabled.
   *
   * @return boolean
   */
  public function isEnabled() {
    return $this->enabled;
  }

  /**
   * @param Request $request
   *
   * @return bool
   */
  public function canEnable(Request $request) {
    $config = $this->configFactory->get('xhprof.config');

    if (extension_loaded('xhprof') && $config->get('enabled') && $this->requestMatcher->matches($request)) {
      $interval = $config->get('interval');

      if ($interval && mt_rand(1, $interval) % $interval != 0) {
        return FALSE;
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param string $run_id
   * @param string $type
   *
   * @return string
   */
  public function link($run_id, $type = 'link') {
    $url = url(XHPROF_PATH . '/' . $run_id, array(
      'absolute' => TRUE,
    ));
    return $type == 'url' ? $url : l(t('XHProf output'), $url);
  }

  /**
   * @return \Drupal\xhprof\XHProfLib\Storage\StorageInterface
   */
  public function getStorage() {
    return $this->storage;
  }

  /**
   * @return string
   */
  public function getRunId() {
    return $this->runId;
  }

  /**
   * @return string
   */
  public function createRunId() {
    if (!$this->runId) {
      $this->runId = uniqid();
    }

    return $this->runId;
  }

}
