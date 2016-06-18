<?php

/**
 * @file
 * Contains \Drupal\loremipsum\Form\LoremIpsumForm.
 */

namespace Drupal\reuse_migrate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ReuseMigrateForm extends ConfigFormBase {

    /**
     * {@inheritdoc}.
     */
    public function getFormId() {
        return 'reuse_migrate_form';
    }

    /**
     * {@inheritdoc}.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Form constructor
        $form = parent::buildForm($form, $form_state);
        // Default settings
        $config = $this->config('reuse_migrate.settings');

        $form['origin_db_host'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Host from D6 origin data base:'),
            '#default_value' => $config->get('reuse_migrate.origin_db_host'),
            '#description' => $this->t('Use "http://" at the begining'),
        );
        $form['origin_db_user'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('User from D6 origin data base:'),
            '#default_value' => $config->get('reuse_migrate.origin_db_user'),
        );
        $form['origin_db_psswd'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Password from D6 origin data base:'),
            '#default_value' => $config->get('reuse_migrate.origin_db_psswd'),
        );
        $form['origin_db_db'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('DB name from D6 origin data base:'),
            '#default_value' => $config->get('reuse_migrate.origin_db_db'),
        );
        $form['origin_db_port'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Port from D6 origin data base:'),
            '#default_value' => $config->get('reuse_migrate.origin_db_port'),
            '#description' => $this->t('If empty, 3306 will be used'),
        );

        return $form;
    }

    /**
     * {@inheritdoc}.
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $config = $this->config('reuse_migrate.settings');
        $config->set('reuse_migrate.origin_db_host', $form_state->getValue('origin_db_host'));
        $config->set('reuse_migrate.origin_db_user', $form_state->getValue('origin_db_user'));
        $config->set('reuse_migrate.origin_db_psswd', $form_state->getValue('origin_db_psswd'));
        $config->set('reuse_migrate.origin_db_db', $form_state->getValue('origin_db_db'));
        $config->set('reuse_migrate.origin_db_port', $form_state->getValue('origin_db_port'));
        
        $config->save();
        return parent::submitForm($form, $form_state);
    }

    /**
     * {@inheritdoc}.
     */
    protected function getEditableConfigNames() {
        return [
            'reuse_migrate.settings',
        ];
    }

}