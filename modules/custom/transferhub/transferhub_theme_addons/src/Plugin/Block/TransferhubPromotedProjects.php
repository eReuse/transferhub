<?php
/**
 * Created by PhpStorm.
 * User: albert
 * Date: 27/08/16
 * Time: 15:33
 */

namespace Drupal\transferhub_theme_addons\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * @Block(
 *   id = "transferhub_promoted_projects",
 *   admin_label = @Translation("Transferhub promoted projects"),
 * )
 */
class TransferhubPromotedProjects extends BlockBase implements BlockPluginInterface {
    /**
     * {@inheritdoc}
     */
    public function build() {

        $config = $this->getConfiguration();

        $view = views_embed_view('projects', 'block_1');
        
        return array(
            '#theme' => "transferhub_promoted_projects",
            '#header_text' => $config["header_text"],
            '#view' => $view,
        );
    }

    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['transferhub_promoted_projects_header_text'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('Header text'),
            //'#description' => $this->t('Who do you want to say hello to?'),
            '#default_value' => isset($config['header_text']) ? $config['header_text'] : ''
        );

        return $form;
    }

    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('header_text', $form_state->getValue('transferhub_promoted_projects_header_text'));
        
    }
}
?>