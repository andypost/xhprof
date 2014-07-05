<?php

namespace Drupal\xhprof\XHProfLib;

/**
 * Class Run
 */
class Run {

  /**
   * @var string
   */
  private $run_id;

  /**
   * @var string
   */
  private $namespace;

  /**
   * @var array
   */
  private $data = array();

  /**
   * @var
   */
  private $parser;

  /**
   * @param string $run_id
   * @param string $namespace
   * @param array $data
   */
  public function __construct($run_id, $namespace, $data) {
    $this->run_id = $run_id;
    $this->namespace = $namespace;
    $this->data = $data;
  }

  /**+
   * @return array
   */
  public function getKeys() {
    return array_keys($this->data);
  }

  /**
   * @param string $key
   *
   * @return array
   */
  public function getMetrics($key) {
    return $this->data[$key];
  }

  /**
   * @return array
   */
  public function getData() {
    return $this->data;
  }

}
