<?php

namespace Drupal\xhprof\Routing;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\xhprof\XHProfLib\XHProf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class RunConverter implements ParamConverterInterface {

  /**
   * @var \Drupal\xhprof\XHProfLib\XHProf
   */
  private $xhprof;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @param \Drupal\xhprof\XHProfLib\XHProf $xhprof
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(XHProf $xhprof, ConfigFactoryInterface $config_factory) {
    $this->xhprof = $xhprof;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults, Request $request) {
    try {
      $namespace = $this->configFactory->get('system.site')->get('name');
      return $this->xhprof->getStorage()->getRun($value, $namespace);
    } catch(\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    if (!empty($definition['type']) && $definition['type'] === 'xhprof:run_id') {
      return TRUE;
    }
    return FALSE;
  }
}
