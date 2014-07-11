<?php

namespace Drupal\xhprof\XHProfLib\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

/**
 * Class StorageFactory
 */
class StorageFactory {

  /**
   *
   */
  public function __construct() {
    $this->storages = array();
  }

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface
   */
  final public static function getStorage(ConfigFactoryInterface $config, ContainerInterface $container) {
    $storage = $config->get('xhprof.config')->get('storage') ? : 'xhprof.file_storage';

    return $container->get($storage);
  }

}
