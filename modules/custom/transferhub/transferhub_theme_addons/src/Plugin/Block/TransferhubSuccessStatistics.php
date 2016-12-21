<?php

namespace Drupal\transferhub_theme_addons\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * @Block(
 *   id = "transferhub_success_statistics",
 *   admin_label = @Translation("Transferhub Success Statistics"),
 * )
 */
class TransferhubSuccessStatistics extends BlockBase implements BlockPluginInterface {
    /**
     * {@inheritdoc}
     */
    public function build() {

        $config = $this->getConfiguration();

        return array(
            '#theme' => "transferhub_success_statistics",
            '#administered' => $config["administered"],
            '#donated' => $config["donated"],
            '#helped' => $config["helped"],
            '#successful' => $config["successful"],
        );
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['transferhub_success_statistics_administered'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Administered devices'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['administered']) ? $config['administered'] : ''
        );
        $form['transferhub_success_statistics_donated'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Donated devices'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['donated']) ? $config['donated'] : ''
        );
        $form['transferhub_success_statistics_helped'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Helped projects'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['helped']) ? $config['helped'] : ''
        );
        $form['transferhub_success_statistics_successful'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Successful projects'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['successful']) ? $config['successful'] : ''
        );

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('administered', $form_state->getValue('transferhub_success_statistics_administered'));
        $this->setConfigurationValue('donated', $form_state->getValue('transferhub_success_statistics_donated'));
        $this->setConfigurationValue('helped', $form_state->getValue('transferhub_success_statistics_helped'));
        $this->setConfigurationValue('successful', $form_state->getValue('transferhub_success_statistics_successful'));
    }
}
?>