<?php

namespace Drupal\xhprof\XHProfLib\Report;

use Drupal\xhprof\XHProfLib\Run;

/**
 * Class ReportEngine
 */
class ReportEngine {

  /**
   * @param $url_params
   * @param $source
   * @param Run $run
   * @param $wts
   * @param $symbol
   * @param $sort
   * @param Run $run1
   * @param Run $run2
   *
   * @return ReportInterface
   */
  public function getReport($url_params, $source, Run $run, $wts, $symbol, $sort = 'wt', Run $run1 = NULL, Run $run2 = NULL) {
    $report = NULL;

    // specific run to display?
    if ($run) {
      $report = new Report($run, $sort, $symbol);
    }
    // diff report for two runs
    else {
      if ($run1 && $run2) {
        $report = new DiffReport($url_params, $run1->getData(), '', $run2->getData(), '', $symbol, $sort, $run1, $run2);
      }
    }

    return $report;
  }
}
