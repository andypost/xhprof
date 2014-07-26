<?php

/**
 * @file
 * Contains \Drupal\xhprof\Controller\XHProfController.
 */

namespace Drupal\xhprof\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\xhprof\XHProfLib\Report\ReportEngine;
use Drupal\xhprof\XHProfLib\Report\ReportInterface;
use Drupal\xhprof\XHProfLib\Run;
use Drupal\xhprof\XHProfLib\XHProf;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class XHProfController
 */
class XHProfController extends ControllerBase {

  /**
   * @var \Drupal\xhprof\XHProfLib\XHProf
   */
  private $xhprof;

  /**
   * @var \Drupal\xhprof\XHProfLib\Report\ReportEngine
   */
  private $reportEngine;

  protected $descriptions = array(
    "fn" => "Function Name",
    "ct" => "Calls",
    "ct_perc" => "Calls%",
    "wt" => "Incl. Wall Time<br>(microsec)",
    "wt_perc" => "IWall%",
    "excl_wt" => "Excl. Wall Time<br>(microsec)",
    "excl_wt_perc" => "EWall%",
    "ut" => "Incl. User<br>(microsecs)",
    "ut_perc" => "IUser%",
    "excl_ut" => "Excl. User<br>(microsec)",
    "excl_ut_perc" => "EUser%",
    "st" => "Incl. Sys <br>(microsec)",
    "st_perc" => "ISys%",
    "excl_st" => "Excl. Sys <br>(microsec)",
    "excl_st_perc" => "ESys%",
    "cpu" => "Incl. CPU<br>(microsecs)",
    "cpu_perc" => "ICpu%",
    "excl_cpu" => "Excl. CPU<br>(microsec)",
    "excl_cpu_perc" => "ECPU%",
    "mu" => "Incl.<br>MemUse<br>(bytes)",
    "mu_perc" => "IMemUse%",
    "excl_mu" => "Excl.<br>MemUse<br>(bytes)",
    "excl_mu_perc" => "EMemUse%",
    "pmu" => "Incl.<br> PeakMemUse<br>(bytes)",
    "pmu_perc" => "IPeakMemUse%",
    "excl_pmu" => "Excl.<br>PeakMemUse<br>(bytes)",
    "excl_pmu_perc" => "EPeakMemUse%",
    "samples" => "Incl. Samples",
    "samples_perc" => "ISamples%",
    "excl_samples" => "Excl. Samples",
    "excl_samples_perc" => "ESamples%",
  );

  protected $diff_descriptions = array(
    "fn" => "Function Name",
    "ct" => "Calls Diff",
    "Calls%" => "Calls<br>Diff%",
    "wt" => "Incl. Wall<br>Diff<br>(microsec)",
    "IWall%" => "IWall<br> Diff%",
    "excl_wt" => "Excl. Wall<br>Diff<br>(microsec)",
    "EWall%" => "EWall<br>Diff%",
    "ut" => "Incl. User Diff<br>(microsec)",
    "IUser%" => "IUser<br>Diff%",
    "excl_ut" => "Excl. User<br>Diff<br>(microsec)",
    "EUser%" => "EUser<br>Diff%",
    "cpu" => "Incl. CPU Diff<br>(microsec)",
    "ICpu%" => "ICpu<br>Diff%",
    "excl_cpu" => "Excl. CPU<br>Diff<br>(microsec)",
    "ECpu%" => "ECpu<br>Diff%",
    "st" => "Incl. Sys Diff<br>(microsec)",
    "ISys%" => "ISys<br>Diff%",
    "excl_st" => "Excl. Sys Diff<br>(microsec)",
    "ESys%" => "ESys<br>Diff%",
    "mu" => "Incl.<br>MemUse<br>Diff<br>(bytes)",
    "IMUse%" => "IMemUse<br>Diff%",
    "excl_mu" => "Excl.<br>MemUse<br>Diff<br>(bytes)",
    "EMUse%" => "EMemUse<br>Diff%",
    "pmu" => "Incl.<br> PeakMemUse<br>Diff<br>(bytes)",
    "IPMUse%" => "IPeakMemUse<br>Diff%",
    "excl_pmu" => "Excl.<br>PeakMemUse<br>Diff<br>(bytes)",
    "EPMUse%" => "EPeakMemUse<br>Diff%",
    "samples" => "Incl. Samples Diff",
    "ISamples%" => "ISamples Diff%",
    "excl_samples" => "Excl. Samples Diff",
    "ESamples%" => "ESamples Diff%",
  );

  protected $format_cbk = array(
    "fn" => "",
    "ct" => array("Drupal\\xhprof\\Controller\\XHProfController", "countFormat"),
    "ct_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "wt" => "number_format",
    "wt_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_wt" => "number_format",
    "excl_wt_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "ut" => "number_format",
    "ut_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_ut" => "number_format",
    "excl_ut_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "st" => "number_format",
    "st_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_st" => "number_format",
    "excl_st_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "cpu" => "number_format",
    "cpu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_cpu" => "number_format",
    "excl_cpu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "mu" => "number_format",
    "mu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_mu" => "number_format",
    "excl_mu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "pmu" => "number_format",
    "pmu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_pmu" => "number_format",
    "excl_pmu_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "samples" => "number_format",
    "samples_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
    "excl_samples" => "number_format",
    "excl_samples_perc" => array("Drupal\\xhprof\\Controller\\XHProfController", "percentFormat"),
  );

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('xhprof.xhprof'),
      $container->get('xhprof.report_engine')
    );
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\XHProf $xhprof
   * @param \Drupal\xhprof\XHProfLib\Report\ReportEngine $reportEngine
   */
  public function __construct(XHProf $xhprof, ReportEngine $reportEngine) {
    $this->xhprof = $xhprof;
    $this->reportEngine = $reportEngine;
  }

  /**
   *
   */
  public function runsAction() {
    $runs = $run = $this->xhprof->getStorage()->getRuns();

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

    $build['table'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#attributes' => $attributes
    );

    return $build;
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Run $run
   *
   * @return string
   */
  public function viewAction(Run $run) {
    $report = $this->reportEngine->getReport(NULL, NULL, $run, NULL, NULL, 'wt', NULL, NULL);

    $build['#title'] = $this->t('XHProf view report for %id', array('%id' => $run->getId()));

    $data = $report->getData();

    $build['table'] = array(
      '#theme' => 'table',
      '#header' => $this->getHeader($data['symbols']),
      '#rows' => $this->getRows($data['symbols'], $report),
      '#attributes' => array('class' => array('responsive')),
      '#attached' => array(
        'library' => array(
          'xhprof/xhprof',
        ),
      ),
    );

    return $build;
  }

  /**
   * @param $table
   *
   * @return array
   */
  private function getHeader($table) {
    return array(
      $this->getDescription('fn'),
      $this->getDescription('ct'),
      $this->getDescription('ct_perc'),
      $this->getDescription('wt'),
      $this->getDescription('wt_perc'),
      $this->getDescription('excl_wt'),
      $this->getDescription('excl_wt_perc'),
      $this->getDescription('cpu'),
      $this->getDescription('cpu_perc'),
      $this->getDescription('excl_cpu'),
      $this->getDescription('excl_cpu_perc'),
      $this->getDescription('mu'),
      $this->getDescription('mu_perc'),
      $this->getDescription('excl_mu'),
      $this->getDescription('excl_mu_perc'),
      $this->getDescription('pmu'),
      $this->getDescription('pmu_perc'),
      $this->getDescription('excl_pmu'),
      $this->getDescription('excl_pmu_perc'),
    );
  }

  /**
   * @param $table
   * @param $report
   *
   * @return array
   */
  private function getRows($table, ReportInterface $report) {
    $rows = array();
    $totals = $report->getTotals();

    foreach ($table as $key => $value) {
      $row = array();
      $row[] = $this->abbrClass($key);

      $row[] = $this->getValue($value['ct'], 'ct');
      $row[] = $this->getPercentValue($value['ct'], 'ct', $totals['ct']);

      $row[] = $this->getValue($value['wt'], 'wt');
      $row[] = $this->getPercentValue($value['wt'], 'wt', $totals['wt']);

      $row[] = $this->getValue($value['excl_wt'], 'excl_wt');
      $row[] = $this->getPercentValue($value['excl_wt'], 'excl_wt', $totals['wt']);

      $row[] = $this->getValue($value['cpu'], 'cpu');
      $row[] = $this->getPercentValue($value['cpu'], 'cpu', $totals['cpu']);

      $row[] = $this->getValue($value['excl_cpu'], 'excl_cpu');
      $row[] = $this->getPercentValue($value['excl_cpu'], 'excl_cpu', $totals['cpu']);

      $row[] = $this->getValue($value['mu'], 'mu');
      $row[] = $this->getPercentValue($value['mu'], 'mu', $totals['mu']);

      $row[] = $this->getValue($value['excl_mu'], 'excl_mu');
      $row[] = $this->getPercentValue($value['excl_mu'], 'excl_mu', $totals['mu']);

      $row[] = $this->getValue($value['pmu'], 'pmu');
      $row[] = $this->getPercentValue($value['pmu'], 'pmu', $totals['pmu']);

      $row[] = $this->getValue($value['excl_pmu'], 'excl_pmu');
      $row[] = $this->getPercentValue($value['excl_pmu'], 'excl_pmu', $totals['pmu']);

      $rows[] = $row;
    }

    return $rows;
  }

  /**
   * @param string $class
   *
   * @return string
   */
  private function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    if (strlen($short) >= 40) {
      $short = substr($short, 0, 30) . " … " . substr($short, -5);
    }

    return String::format('<abbr title="@class">@short</abbr>', array('@class' => $class, '@short' => $short));
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Run $run1
   * @param \Drupal\xhprof\XHProfLib\Run $run2
   *
   * @return string
   */
  public function diffAction(Run $run1, Run $run2) {
    //drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');

    return ''; //xhprof_display_run(array($run1, $run2), $symbol = NULL);
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Run $run
   * @param $symbol
   *
   * @return string
   */
  public function symbolAction(Run $run, $symbol) {
    //drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');

    return ''; //xhprof_display_run(array($run_id), $symbol);
  }

  /**
   * @param $metric
   *
   * @return string
   */
  private function getDescription($metric) {
    return $this->t($this->descriptions[$metric]);
  }

  /**
   * @param $value
   * @param $metric
   *
   * @return mixed
   */
  private function getValue($value, $metric) {
    return call_user_func($this->format_cbk[$metric], $value);
  }

  /**
   * @param $value
   * @param $metric
   * @param $totals
   *
   * @return mixed|string
   */
  private function getPercentValue($value, $metric, $totals) {
    if ($totals == 0) {
      $pct = "N/A%";
    }
    else {
      $pct = call_user_func($this->format_cbk[$metric . '_perc'], ($value / abs($totals)));
    }

    return $pct;
  }

  /**
   * @param $num
   * @return string
   */
  private function countFormat($num) {
    $num = round($num, 3);
    if (round($num) == $num) {
      return number_format($num);
    }
    else {
      return number_format($num, 3);
    }
  }

  /**
   * @param $s
   * @param int $precision
   * @return string
   */
  private function percentFormat($s, $precision = 1) {
    return sprintf('%.' . $precision . 'f%%', 100 * $s);
  }
}
