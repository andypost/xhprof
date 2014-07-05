<?php

namespace Drupal\xhprof\Controller;

use Drupal\Core\Controller\ControllerBase;
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('xhprof.xhprof')
    );
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\XHProf $xhprof
   */
  public function __construct(XHProf $xhprof) {
    $this->xhprof = $xhprof;
  }

  /**
   *
   */
  public function runsAction() {
    $runs = $run = $this->xhprof->getActiveStorage()->getRuns();

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
    //drupal_add_css(drupal_get_path('module', 'xhprof') . '/xhprof.css');

    //var_dump($run->getKeys());

    return ''; //xhprof_display_run(array($run_id), NULL);
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
}
