<?php
/**
 * @file
 * Contains \Drupal\loremipsum\Form\BlockFormController
 */

namespace Drupal\transferhub_vote\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\bootstrap\Bootstrap;

/**
 * Lorem Ipsum block form
 */
class TransferHubVoteAnonymousForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'transferhub_vote_anonymous_block_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        // How many paragraphs?
        // $options = new array();
        // Submit

        $form['referer'] = array(
            '#type' => 'hidden',
            //'#value' => \Drupal::service('path.current')->getPath(),
            '#value' => \Drupal::request()->getRequestUri()
        );

        $icon = array(
            '#type' => "html_tag",
            '#tag' => "span",
            '#value' => "",
            '#attributes' =>  array(
                 'class' => array("fa","fa-facebook")
                )
            );

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Login to Facebook to vote'),
            "#icon" => array(
                '#type' => "html_tag",
                '#tag' => "span",
                '#value' => "",
                '#attributes' =>  array(
                    'class' => array("fa","fa-facebook")
                )
            ),
           '#attributes' => array("class" => array("btn", "btn-block", "btn-social", "btn-lg", "btn-facebook"))
        );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {

        $_SESSION['transferhub_vote']['returnUrl'] = $form_state->getValue("referer");

        $form_state->setRedirect(
            'transferhub_vote.login'
        );
    }
}