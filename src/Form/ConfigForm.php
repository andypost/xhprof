<?php

namespace Drupal\xhprof\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\xhprof\XHProfLib\XHProf;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigForm
 */
class ConfigForm extends ConfigFormBase {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'xhprof_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->config('xhprof.config');

    $description = extension_loaded('xhprof') ? t('Profile requests with the xhprof php extension.') : '<span class="warning">' . t('You must enable the <a href="!url">xhprof php extension</a> to use this feature.', array('!url' => url('http://techportal.ibuildings.com/2009/12/01/profiling-with-xhprof/'))) . '</span>';
    $form['xhprof_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable profiling of page views and <a href="!drush">drush</a> requests.', array('!drush' => url('https://github.com/drush-ops/drush'))),
      '#default_value' => $config->get('xhprof_enabled'),
      '#description' => $description,
      '#disabled' => !extension_loaded('xhprof'),
    );

    $form['settings'] = array(
      '#title' => $this->t('Profiling settings'),
      '#type' => 'details',
      '#open' => TRUE,
      '#states' => array(
        'invisible' => array(
          'input[name="xhprof_enabled"]' => array('checked' => FALSE),
        ),
      ),
    );

    $form['settings']['xhprof_disable_admin_paths'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Disable profiling of admin pages'),
      '#default_value' => $config->get('xhprof_disable_admin_paths'),
    );

    $form['settings']['xhprof_interval'] = array(
      '#type' => 'textfield',
      '#title' => 'Profiling interval',
      '#default_value' => $config->get('xhprof_interval'),
      '#description' => $this->t('The approximate number of requests between XHProf samples. Leave empty to profile all requests'),
    );

    $options = $this->xhprof->getStorages();
    $form['settings']['xhprof_storage'] = array(
      '#type' => 'radios',
      '#title' => $this->t('XHProf storage'),
      '#default_value' => $config->get('xhprof_storage'),
      '#options' => $options,
      '#description' => $this->t('Choose the XHProf storage class.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {
    // TODO: Simplify this.
    if (isset($form_state['values']['xhprof_interval']) && $form_state['values']['xhprof_interval'] != '' && (!is_numeric($form_state['values']['xhprof_interval']) || $form_state['values']['xhprof_interval'] <= 0 || $form_state['values']['xhprof_interval'] > mt_getrandmax())) {
      $this->setFormError('xhprof_interval', $form_state, $this->t('The profiling interval must be set to a positive integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('xhprof.config')
      ->set('xhprof_enabled', $form_state['values']['xhprof_enabled'])
      ->set('xhprof_disable_admin_paths', $form_state['values']['xhprof_disable_admin_paths'])
      ->set('xhprof_interval', $form_state['values']['xhprof_interval'])
      ->set('xhprof_storage', $form_state['values']['xhprof_storage'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
