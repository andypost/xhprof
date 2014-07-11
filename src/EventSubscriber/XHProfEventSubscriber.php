<?php

namespace Drupal\xhprof\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\xhprof\XHProfLib\XHProf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class XHProfEventSubscriber
 */
class XHProfEventSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\xhprof\XHProfLib\XHProf
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
  private $xhprofRunId;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @param \Drupal\xhprof\XHProfLib\XHProf $xhprof
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(XHProf $xhprof, AccountInterface $currentUser, ModuleHandlerInterface $module_handler) {
    $this->xhprof = $xhprof;
    $this->currentUser = $currentUser;
    $this->moduleHandler = $module_handler;
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {
    if($this->xhprof->canEnable($event->getRequest())) {
      $this->xhprof->enable();
    }
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    if ($this->xhprof->isEnabled()) {
      $this->xhprofRunId = $this->xhprof->createRunId();

      // Don't print the link to xhprof run page if
      // Webprofiler module is enabled, a widget will
      // be rendered into Webprofiler toolbar.
      if (!$this->moduleHandler->moduleExists('webprofiler')) {
        $response = $event->getResponse();

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

        if ($this->currentUser->hasPermission('access xhprof data')) {
          $this->injectLink($response, $this->xhprofRunId);
        }
      }

      if (function_exists('drush_log')) {
        drush_log('xhprof link: ' . $this->xhprof->link($this->xhprofRunId, 'url'), 'notice');
      }
    }
  }

  /**
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    if ($this->xhprof->isEnabled()) {
      $this->xhprof->shutdown($this->xhprofRunId);
    }
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
   * @param string $xhprofRunId
   */
  protected function injectLink(Response $response, $xhprofRunId) {
    $content = $response->getContent();
    $pos = mb_strripos($content, '</body>');

    if (FALSE !== $pos) {
      $output = '<div class="xhprof-ui">' . $this->xhprof->link($xhprofRunId) . '</div>';
      $content = mb_substr($content, 0, $pos) . $output . mb_substr($content, $pos);
      $response->setContent($content);
    }
  }
}
