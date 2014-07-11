<?php

namespace Drupal\xhprof\XHProfLib\Report;

abstract class BaseReport implements ReportInterface {

  protected $stats;
  protected $pc_stats;
  protected $metrics;
  protected $diff_mode;
  protected $sort_col;
  protected $display_calls;
  protected $totals;

  protected $sortable_columns = array(
    "fn" => 1,
    "ct" => 1,
    "wt" => 1,
    "excl_wt" => 1,
    "ut" => 1,
    "excl_ut" => 1,
    "st" => 1,
    "excl_st" => 1,
    "mu" => 1,
    "excl_mu" => 1,
    "pmu" => 1,
    "excl_pmu" => 1,
    "cpu" => 1,
    "excl_cpu" => 1,
    "samples" => 1,
    "excl_samples" => 1
  );

  /**
   * @param $xhprof_data
   * @param $rep_symbol
   * @param $sort
   * @param bool $diff_report
   */
  protected function initMetrics($xhprof_data, $rep_symbol, $sort, $diff_report = FALSE) {
    $this->diff_mode = $diff_report;

    if (!empty($sort)) {
      if (array_key_exists($sort, $this->sortable_columns)) {
        $this->sort_col = $sort;
      }
      else {
        print("Invalid Sort Key $sort specified in URL");
      }
    }

    // For C++ profiler runs, walltime attribute isn't present.
    // In that case, use "samples" as the default sort column.
    if (!isset($xhprof_data["main()"]["wt"])) {

      if ($this->sort_col == "wt") {
        $this->sort_col = "samples";
      }

      // C++ profiler data doesn't have call counts.
      // ideally we should check to see if "ct" metric
      // is present for "main()". But currently "ct"
      // metric is artificially set to 1. So, relying
      // on absence of "wt" metric instead.
      $this->display_calls = FALSE;
    }
    else {
      $this->display_calls = TRUE;
    }

    // parent/child report doesn't support exclusive times yet.
    // So, change sort hyperlinks to closest fit.
    if (!empty($rep_symbol)) {
      $this->sort_col = str_replace("excl_", "", $this->sort_col);
    }

    if ($this->display_calls) {
      $this->stats = array("fn", "ct", "Calls%");
    }
    else {
      $this->stats = array("fn");
    }

    $this->pc_stats = $this->stats;

    $possible_metrics = $this->getPossibleMetrics($xhprof_data);
    foreach ($possible_metrics as $metric => $desc) {
      if (isset($xhprof_data["main()"][$metric])) {
        $metrics[] = $metric;
        // flat (top-level reports): we can compute
        // exclusive metrics reports as well.
        $this->stats[] = $metric;
        $this->stats[] = "I" . $desc[0] . "%";
        $this->stats[] = "excl_" . $metric;
        $this->stats[] = "E" . $desc[0] . "%";

        // parent/child report for a function: we can
        // only breakdown inclusive times correctly.
        $this->pc_stats[] = $metric;
        $this->pc_stats[] = "I" . $desc[0] . "%";
      }
    }
  }

  /**
   * @return array
   */
  protected function getPossibleMetrics() {
    return array(
      "wt" => array("Wall", "microsecs", "walltime"),
      "ut" => array("User", "microsecs", "user cpu time"),
      "st" => array("Sys", "microsecs", "system cpu time"),
      "cpu" => array("Cpu", "microsecs", "cpu time"),
      "mu" => array("MUse", "bytes", "memory usage"),
      "pmu" => array("PMUse", "bytes", "peak memory usage"),
      "samples" => array("Samples", "samples", "cpu time")
    );
  }

  /**
   * @param $xhprof_data
   *
   * @return array
   */
  protected function getMetrics($xhprof_data) {
    // get list of valid metrics
    $possible_metrics = $this->getPossibleMetrics();

    // return those that are present in the raw data.
    // We'll just look at the root of the subtree for this.
    $metrics = array();
    foreach ($possible_metrics as $metric => $desc) {
      if (isset($xhprof_data["main()"][$metric])) {
        $metrics[] = $metric;
      }
    }

    return $metrics;
  }

  /**
   * @param $raw_data
   * @param $overall_totals
   * @return array
   */
  protected function computeFlatInfo($raw_data, &$overall_totals) {
    $metrics = $this->getMetrics($raw_data);
    $overall_totals = array(
      "ct" => 0,
      "wt" => 0,
      "ut" => 0,
      "st" => 0,
      "cpu" => 0,
      "mu" => 0,
      "pmu" => 0,
      "samples" => 0
    );

    // Compute inclusive times for each function.
    $symbol_tab = $this->computeInclusiveTimes($raw_data);

    // Total metric value is the metric value for "main()".
    foreach ($metrics as $metric) {
      $overall_totals[$metric] = $symbol_tab["main()"][$metric];
    }

    // Initialize exclusive (self) metric value to inclusive metric value to start with.
    // In the same pass, also add up the total number of function calls.
    foreach ($symbol_tab as $symbol => $info) {
      foreach ($metrics as $metric) {
        $symbol_tab[$symbol]["excl_" . $metric] = $symbol_tab[$symbol][$metric];
      }
      // Keep track of total number of calls.
      $overall_totals["ct"] += $info["ct"];
    }

    // Adjust exclusive times by deducting inclusive time of children.
    foreach ($raw_data as $parent_child => $info) {
      list($parent, $child) = $this->parseParentChild($parent_child);

      if ($parent) {
        foreach ($metrics as $metric) {
          // make sure the parent exists hasn't been pruned.
          if (isset($symbol_tab[$parent])) {
            $symbol_tab[$parent]["excl_" . $metric] -= $info[$metric];
          }
        }
      }
    }

    return $symbol_tab;
  }

  /**
   * @param $parent_child
   *
   * @return array
   */
  protected function parseParentChild($parent_child) {
    $ret = explode("==>", $parent_child);

    // Return if both parent and child are set
    if (isset($ret[1])) {
      return $ret;
    }

    return array(NULL, $ret[0]);
  }

  /**
   * @param $raw_data
   *
   * @return array
   */
  protected function computeInclusiveTimes($raw_data) {
    $metrics = $this->getMetrics($raw_data);

    $symbol_tab = array();

    /*
     * First compute inclusive time for each function and total
     * call count for each function across all parents the
     * function is called from.
     */
    foreach ($raw_data as $parent_child => $info) {
      list($parent, $child) = $this->parseParentChild($parent_child);

      // TODO: is this needed?
      //if ($parent == $child) {
      //  /*
      //   * XHProf PHP extension should never trigger this situation any more.
      //   * Recursion is handled in the XHProf PHP extension by giving nested
      //   * calls a unique recursion-depth appended name (for example, foo@1).
      //   */
      //  watchdog("Error in Raw Data: parent & child are both: %parent", array('%parent' => $parent));
      //  return;
      //}

      if (!isset($symbol_tab[$child])) {
        $symbol_tab[$child] = array("ct" => $info["ct"]);
        foreach ($metrics as $metric) {
          $symbol_tab[$child][$metric] = $info[$metric];
        }
      }
      else {
        // increment call count for this child
        $symbol_tab[$child]["ct"] += $info["ct"];

        // update inclusive times/metric for this child
        foreach ($metrics as $metric) {
          $symbol_tab[$child][$metric] += $info[$metric];
        }
      }
    }

    return $symbol_tab;
  }

  /**
   * @param $raw_data
   * @param $functions_to_keep
   * @return array
   */
  function trimRun($raw_data, $functions_to_keep) {

    // convert list of functions to a hash with function as the key
    $function_map = array_fill_keys($functions_to_keep, 1);

    // always keep main() as well so that overall totals can still
    // be computed if need be.
    $function_map['main()'] = 1;

    $new_raw_data = array();
    foreach ($raw_data as $parent_child => $info) {
      list($parent, $child) = $this->parseParentChild($parent_child);

      if (isset($function_map[$parent]) || isset($function_map[$child])) {
        $new_raw_data[$parent_child] = $info;
      }
    }

    return $new_raw_data;
  }

  /**
   * @param $arr
   * @param $k
   * @param $v
   * @return mixed
   */
  function arraySet($arr, $k, $v) {
    $arr[$k] = $v;
    return $arr;
  }

  /**
   * @param $arr
   * @param $k
   * @return mixed
   */
  function arrayUnset($arr, $k) {
    unset($arr[$k]);
    return $arr;
  }

  /**
   * @return mixed
   */
  public function getTotals() {
    return $this->totals;
  }

}
