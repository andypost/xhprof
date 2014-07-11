<?php

namespace Drupal\xhprof\XHProfLib\Report;

use Drupal\xhprof\XHProfLib\Run;

class Report extends BaseReport {

  private $sort;
  private $run;
  private $symbol;
  private $symbol_tab;

  /**
   * @param $run
   * @param $sort
   */
  public function __construct(Run $run, $sort, $symbol) {
    $this->sort = $sort;
    $this->run = $run;
    $this->symbol = $symbol;

    $this->initMetrics($run->getData(), NULL, $sort);
    $this->profilerReport($run, $sort);
  }

  /**
   * @param $run
   * @param $sort
   */
  public function profilerReport(Run $run, $sort) {
    if (!empty($this->symbol)) {
      $data = $this->trimRun($run->getData(), $this->symbol);
    }
    else {
      $data = $run->getData();
    }

    $this->symbol_tab = $this->computeFlatInfo($data, $this->totals);

    Sorter::sort($this->symbol_tab, $sort);
    $this->symbol_tab = array_slice($this->symbol_tab, 0, 100);

  }

  /**
   * @return array
   */
  public function getData() {
    return array(
      'symbols' => $this->symbol_tab,
      'totals' => $this->totals,
      'possible_metrics' => $this->getPossibleMetrics(),
      'metrics' => $this->metrics,
      'display_calls' => $this->display_calls,
    );
  }

}
