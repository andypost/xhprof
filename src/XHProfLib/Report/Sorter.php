<?php

namespace Drupal\xhprof\XHProfLib\Report;

class Sorter {

  private static $metric;

  static function sort(&$symbols, $metric) {
    self::$metric = $metric;
    uasort($symbols, array("Drupal\\xhprof\\XHProfLib\\Report\\Sorter", "cmp_method"));
  }

  static function cmp_method($a, $b) {
    $metric = self::$metric;

    if ($metric == "fn") {

      // case insensitive ascending sort for function names
      $left = strtoupper($a["fn"]);
      $right = strtoupper($b["fn"]);

      if ($left == $right) {
        return 0;
      }

      return ($left < $right) ? -1 : 1;
    }
    else {
      // descending sort for all others
      $left = $a[$metric];
      $right = $b[$metric];

      if ($left == $right) {
        return 0;
      }
      return ($left > $right) ? -1 : 1;
    }
  }

}
