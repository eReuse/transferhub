<?php
/**
 * @file
 * Contains \Drupal\loremipsum\Form\BlockFormController
 */

namespace Drupal\transferhub_vote\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

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
        return 'transferhub_vote_block_form';
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

        $form['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Login to Facebook to vote'),
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
            'reuse_migrate.login'
        );
    }
}