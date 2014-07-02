<?php

namespace Drupal\xhprof\XHProfLib\Runs;

interface RunsInterface {

  public function getRuns();

  public function getRun($run_id, $namespace);

  public function saveRun($data, $namespace, $run_id);
}
