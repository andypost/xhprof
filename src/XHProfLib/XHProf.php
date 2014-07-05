<?php

namespace Drupal\xhprof\XHProfLib;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\xhprof\XHProfLib\Storage\StorageInterface;

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
   * @var array
   */
  private $storages;

  /**
   * @var string
   */
  private $runId;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\xhprof\XHProfLib\Storage\StorageInterface $storage
   */
  public function __construct(ConfigFactoryInterface $configFactory, StorageInterface $storage) {
    $this->configFactory = $configFactory;
    $this->storage = $storage;
    $this->storages = array();
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
   *
   * @return boolean
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
   * @param string $run_id
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
   * @return array
   */
  public function shutdown($runId) {
    $namespace = $this->configFactory->get('system.site')->get('name'); // namespace for your application
    $xhprof_data = xhprof_disable();
    return $this->storage->saveRun($xhprof_data, $namespace, $runId);
  }

  /**
   * @return array
   */
  public function getStorages() {
    $output = array();

    /** @var \Drupal\xhprof\XHProfLib\Storage\StorageInterface $storage */
    foreach ($this->storages as $id => $storage) {
      $output[$id] = $storage->getName();
    }

    return $output;
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Storage\StorageInterface $storage
   */
  public function addStorage($id, StorageInterface $storage) {
    $this->storages[$id] = $storage;
  }

  /**
   * @return \Drupal\xhprof\XHProfLib\Storage\StorageInterface
   */
  public function getActiveStorage() {
    $id = $this->configFactory->get('xhprof.config')->get('xhprof_storage');
    return $this->storages[$id];
  }

  /**
   * @return string
   */
  public function getRunId() {
    if(!$this->runId) {
      $this->runId = uniqid();
    }

    return $this->runId;
  }
}
