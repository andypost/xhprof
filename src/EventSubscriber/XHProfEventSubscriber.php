<?php

namespace Drupal\xhprof\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\xhprof\XHProfLib\XHProf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class XHProfEventSubscriber
 */
class XHProfEventSubscriber implements EventSubscriberInterface {

  /**
   * @var XHProf
   */
  public $xhprof;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var string
   */
  private $xhprof_run_id;

  public function __construct(XHProf $xhprof, AccountInterface $currentUser, UrlGeneratorInterface $urlGenerator) {
    $this->xhprof = $xhprof;
    $this->currentUser = $currentUser;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * @param GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $this->xhprof->enable();
  }

  /**
   * @param FilterResponseEvent $event
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    if ($this->xhprof->isEnabled()) {
      $response = $event->getResponse();
      $this->xhprof_run_id = $this->runId();

      // Try not to break non html pages.
      $formats = array(
        'xml',
        'javascript',
        'json',
        'plain',
        'image',
        'application',
        'csv',
        'x-comma-separated-values'
      );
      foreach ($formats as $format) {
        if ($response->headers->get($format)) {
          return;
        }
      }

      if (function_exists('drush_log')) {
        drush_log('xhprof link: ' . $this->xhprof->link($this->xhprof_run_id, 'url'), 'notice');
      }

      if ($this->currentUser->hasPermission('access xhprof data')) {
        $this->injectLink($response, $this->xhprof_run_id);
      }
    }
  }

  /**
   * @param PostResponseEvent $event
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    $this->xhprof->shutdown($this->xhprof_run_id);
  }

  /**
   * @return array
   */
  static function getSubscribedEvents() {
    return array(
      KernelEvents::REQUEST => array('onKernelRequest', 0),
      KernelEvents::RESPONSE => array('onKernelResponse', 0),
      KernelEvents::TERMINATE => array('onKernelTerminate', 0),
    );
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Response $response
   */
  protected function injectLink(Response $response, $xhprof_run_id) {
    $content = $response->getContent();
    $pos = mb_strripos($content, '</body>');

    if (FALSE !== $pos) {
      $output = '<div class="xhprof-ui">' . $this->xhprof->link($xhprof_run_id) . '</div>';
      $content = mb_substr($content, 0, $pos) . $output . mb_substr($content, $pos);
      $response->setContent($content);
    }
  }

  /**
   * @return string
   */
  private function runId() {
    return uniqid();
  }
}
