<?php

namespace Drupal\transferhub_theme_addons\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * @Block(
 *   id = "transferhub_quote",
 *   admin_label = @Translation("Transferhub quote"),
 * )
 */
class TransferhubQuote extends BlockBase implements BlockPluginInterface {
    /**
     * {@inheritdoc}
     */
    public function build() {

        $config = $this->getConfiguration();

        return array(
            '#theme' => "transferhub_quote",
            '#text' => $config["text"],
            '#author' => $config["author"],
            '#position' => $config["position"],
        );
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['transferhub_quote_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Text'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['text']) ? $config['text'] : ''
        );
        $form['transferhub_quote_author'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Author'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['author']) ? $config['author'] : ''
        );
        $form['transferhub_quote_position'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Position'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['position']) ? $config['position'] : ''
        );

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('text', $form_state->getValue('transferhub_quote_text'));
        $this->setConfigurationValue('author', $form_state->getValue('transferhub_quote_author'));
        $this->setConfigurationValue('position', $form_state->getValue('transferhub_quote_position'));
    }
}
?>