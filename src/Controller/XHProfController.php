<?php

namespace Drupal\xhprof\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class XHProfController
 */
class XHProfController extends ControllerBase {

  /**
   *
   */
  public function runsAction() {
    global $pager_page_array, $pager_total, $pager_total_items;
    xhprof_include();
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    $element = 0;
    $limit = 50;

    $class = $this->config('xhprof.config')->get('xhprof_default_class');
    $xhprof_runs_impl = new $class();
    $pager_page_array = array($page);
    $pager_total_items[$element] = $xhprof_runs_impl->getCount();
    $pager_total[$element] = ceil($pager_total_items[$element] / $limit);
    $pager_start = $page * 50;
    $pager_end = $pager_start + 50;
    $runs = $xhprof_runs_impl->getRuns(array(), $limit);

    // Set the pager info in these globals since we need to fake them for
    // theme_pager.
    // Table attributes
    $attributes = array('id' => 'xhprof-runs-table');

    // Table header
    $header = array();
    $header[] = array('data' => t('View'));
    $header[] = array('data' => t('Path'), 'field' => 'path');
    $header[] = array('data' => t('Date'), 'field' => 'date', 'sort' => 'desc');

    // Table rows
    $rows = array();
    foreach ($runs as $run) {
      $row = array();
      $link = XHPROF_PATH . '/' . $run['run_id'];
      $row[] = array('data' => l($run['run_id'], $link));
      $row[] = array('data' => isset($run['path']) ? $run['path'] : '');
      $row[] = array('data' => format_date($run['date'], 'small'));
      $rows[] = $row;
    }

    $output = theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => $attributes));
    $output .= theme('pager');
    return $output;
  }

  /**
   * @param $run_id
   *
   * @return string
   */
  public function viewAction($run_id) {
    drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');
    return xhprof_display_run(array($run_id), NULL);
  }

  /**
   * @param $run1
   * @param $run2
   *
   * @return string
   */
  public function diffAction($run1, $run2) {
    drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');
    return xhprof_display_run(array($run1, $run2), $symbol = NULL);
  }

  /**
   * @param $run_id
   * @param $symbol
   *
   * @return string
   */
  public function pageAction($run_id, $symbol) {
    drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');
    return xhprof_display_run(array($run_id), $symbol);
  }
}
