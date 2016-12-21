<?php

namespace Drupal\transferhub_theme_addons\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * @Block(
 *   id = "transferhub_video",
 *   admin_label = @Translation("Transferhub Video"),
 * )
 */
class TransferhubVideo extends BlockBase implements BlockPluginInterface {
    /**
     * {@inheritdoc}
     */
    public function build() {

        $config = $this->getConfiguration();        

        return array(
            '#theme' => "transferhub_video",
            '#text' => $config["text"],
            '#link' => $config["link"],            
        );
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['transferhub_video_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Text'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['text']) ? $config['text'] : ''
        );
        $form['transferhub_video_link'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Video link'),
            '#description' => $this->t('Youtube, Vimeo'),
            '#default_value' => isset($config['link']) ? $config['link'] : ''
        );
        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('text', $form_state->getValue('transferhub_video_text'));
        $this->setConfigurationValue('link', $form_state->getValue('transferhub_video_link'));
    }
}
?>