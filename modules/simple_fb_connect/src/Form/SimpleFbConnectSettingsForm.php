<?php

/**
 * @file
 * Contains \Drupal\simple_fb_connect\Form\SimpleFbConnectSettingsForm.
 */

namespace Drupal\simple_fb_connect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Defines a form that configures Simple FB Connect settings.
 */
class SimpleFbConnectSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_fb_connect_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_fb_connect.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    drupal_set_message(t('Installation and configuration instructions can be found from the README.txt'));

    $simple_fb_config = $this->config('simple_fb_connect.settings');

    $form['fb_settings'] = array(
      '#type' => 'details',
      '#title' => t('Facebook App settings'),
      '#open' => TRUE,
      '#description' => t('You need to first create a Facebook App at <a href="https://developers.facebook.com/apps">https://developers.facebook.com/apps</a>'),
    );

    $form['fb_settings']['app_id'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Application ID'),
      '#default_value' => $simple_fb_config->get('app_id'),
      '#description' => t('Copy the App ID of your Facebook App here'),
    );

    $form['fb_settings']['app_secret'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('App Secret'),
      '#default_value' => $simple_fb_config->get('app_secret'),
      '#description' => t('Copy the App Secret of your Facebook App here'),
    );

    $form['fb_settings']['site_url'] = array(
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#title' => t('Site URL'),
      '#description' => t('Copy this value to <em>Site URL</em> and <em>Mobile Site URL</em> of your Facebook App settings.'),
      '#default_value' => $GLOBALS['base_url'],
    );

    $form['module_settings'] = array(
      '#type' => 'details',
      '#title' => t('Simple FB Connect configurations'),
      '#open' => TRUE,
      '#description' => t('These settings allow you to configure how Simple FB Connect module behaves on your Drupal site'),
    );

    $form['module_settings']['post_login_path'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Post login path'),
      '#description' => t('Drupal path where the user should be redirected after successful login. Use <em>&lt;front&gt;</em> to redirect user to your front page.'),
      '#default_value' => $simple_fb_config->get('post_login_path'),
    );

    $form['module_settings']['login_only'] = array(
      '#type' => 'checkbox',
      '#title' => t('Login Only (No Registration)'),
      '#description' => t('Allow only existing users to login with FB. New users can not register using FB login.'),
      '#default_value' => $simple_fb_config->get('login_only'),
    );

    $form['module_settings']['disable_admin_login'] = array(
      '#type' => 'checkbox',
      '#title' => t('Disable FB login for administrator'),
      '#description' => t('Disabling FB login for administrator (<em>user 1</em>) can help protect your site if a security vulnerability is ever discovered in Facebook PHP SDK or this module.'),
      '#default_value' => $simple_fb_config->get('disable_admin_login'),
    );

    // Option to disable FB login for specific roles.
    $roles = user_roles();
    $options = array();
    foreach ($roles as $key => $role_object) {
      if ($key != 'anonymous' && $key != 'authenticated') {
        $options[$key] = SafeMarkup::checkPlain($role_object->get('label'));
      }
    }

    $form['module_settings']['disabled_roles'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Disable FB login for the following roles'),
      '#options' => $options,
      '#default_value' => $simple_fb_config->get('disabled_roles'),
    );
    if (empty($roles)) {
      $form['module_settings']['disabled_roles']['#description'] = t('No roles found.');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('simple_fb_connect.settings')
      ->set('app_id', $values['app_id'])
      ->set('app_secret', $values['app_secret'])
      ->set('post_login_path', $values['post_login_path'])
      ->set('login_only', $values['login_only'])
      ->set('disable_admin_login', $values['disable_admin_login'])
      ->set('disabled_roles', $values['disabled_roles'])
      ->save();
  }

}
