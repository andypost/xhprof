<?php

namespace Drupal\xhprof\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\xhprof\XHProfLib\Storage\StorageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigForm
 */
class ConfigForm extends ConfigFormBase {

  /**
   * @var \Drupal\xhprof\XHProfLib\Storage\StorageManager
   */
  private $storageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('xhprof.storage_manager')
    );
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Storage\StorageManager $storageManager
   */
  public function __construct(StorageManager $storageManager) {
    $this->storageManager = $storageManager;
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
    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable profiling of page views and <a href="!drush">drush</a> requests.', array('!drush' => url('https://github.com/drush-ops/drush'))),
      '#default_value' => $config->get('enabled'),
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

    $form['settings']['exclude'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#default_value' => $config->get('exclude'),
      '#description' => $this->t('Path to exclude for profiling. One path per line.')
    );

    $form['settings']['interval'] = array(
      '#type' => 'textfield',
      '#title' => 'Profiling interval',
      '#default_value' => $config->get('interval'),
      '#description' => $this->t('The approximate number of requests between XHProf samples. Leave empty to profile all requests'),
    );

    $options = $this->storageManager->getStorages();
    $form['settings']['storage'] = array(
      '#type' => 'radios',
      '#title' => $this->t('XHProf storage'),
      '#default_value' => $config->get('storage'),
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
    if (isset($form_state['values']['interval']) && $form_state['values']['interval'] != '' && (!is_numeric($form_state['values']['interval']) || $form_state['values']['interval'] <= 0 || $form_state['values']['interval'] > mt_getrandmax())) {
      $this->setFormError('interval', $form_state, $this->t('The profiling interval must be set to a positive integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('xhprof.config')
      ->set('enabled', $form_state['values']['enabled'])
      ->set('exclude', $form_state['values']['exclude'])
      ->set('interval', $form_state['values']['interval'])
      ->set('storage', $form_state['values']['storage'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
