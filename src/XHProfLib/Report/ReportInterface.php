<?php

namespace Drupal\xhprof\XHProfLib\Report;

interface ReportInterface {

  public function getData();

  public function getTotals();
}
