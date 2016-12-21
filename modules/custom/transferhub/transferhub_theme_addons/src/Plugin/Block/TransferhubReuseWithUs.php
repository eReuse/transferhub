<?php

namespace Drupal\transferhub_theme_addons\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * @Block(
 *   id = "transferhub_reuse_with_us",
 *   admin_label = @Translation("Transferhub Reuse with us"),
 * )
 */
class TransferhubReuseWithUs extends BlockBase implements BlockPluginInterface {
    /**
     * {@inheritdoc}
     */
    public function build() {

        $config = $this->getConfiguration();

        return array(
            '#theme' => "transferhub_reuse_with_us",
            '#first' => $config["first"],
            '#second' => $config["second"],
            '#third' => $config["third"],
            '#fourth' => $config["fourth"],
        );
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['transferhub_reuse_with_us_first_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('First text'),
            '#description' => $this->t('Text between * will be highlighted'),
            '#default_value' => isset($config['first']) ? $config['first'] : ''
        );
        $form['transferhub_reuse_with_us_second_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Second text'),
            '#description' => $this->t('Text between * will be highlighted'),
            '#default_value' => isset($config['second']) ? $config['second'] : ''
        );
        $form['transferhub_reuse_with_us_third_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Third text'),
            '#description' => $this->t('Text between * will be highlighted'),
            '#default_value' => isset($config['third']) ? $config['third'] : ''
        );
        $form['transferhub_reuse_with_us_fourth_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Fourth text'),
            '#description' => $this->t('Text between * will be highlighted'),
            '#default_value' => isset($config['fourth']) ? $config['fourth'] : ''
        );

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('first', $form_state->getValue('transferhub_reuse_with_us_first_text'));
        $this->setConfigurationValue('second', $form_state->getValue('transferhub_reuse_with_us_second_text'));
        $this->setConfigurationValue('third', $form_state->getValue('transferhub_reuse_with_us_third_text'));
        $this->setConfigurationValue('fourth', $form_state->getValue('transferhub_reuse_with_us_fourth_text'));
    }
}
?>