<?php

namespace Drupal\xhprof\Form;

use Drupal\Core\Form\ConfigFormBase;

class ConfigForm extends ConfigFormBase {

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

    $classes = xhprof_get_classes();
    $options = array_combine($classes, $classes);
    $form['settings']['xhprof_default_class'] = array(
      '#type' => 'radios',
      '#title' => $this->t('XHProf storage'),
      '#default_value' => $config->get('xhprof_default_class'),
      '#options' => $options,
      '#description' => $this->t('Choose an XHProf runs class.'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('xhprof.config')
      ->set('xhprof_enabled', $form_state['values']['xhprof_enabled'])
      ->set('xhprof_disable_admin_paths', $form_state['values']['xhprof_disable_admin_paths'])
      ->set('xhprof_interval', $form_state['values']['xhprof_interval'])
      ->set('xhprof_default_class', $form_state['values']['xhprof_default_class'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
