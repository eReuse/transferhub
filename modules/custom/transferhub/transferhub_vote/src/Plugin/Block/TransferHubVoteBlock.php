<?php

namespace Drupal\transferhub_vote\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a Lorem ipsum block with which you can generate dummy text anywhere
 *
 * @Block(
 *   id = "loremipsum_block",
 *   admin_label = @Translation("Lorem ipsum block"),
 * )
 */

class TransferHubVoteBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        
        $userId = \Drupal::currentUser()->id();
        $nodeId = \Drupal::routeMatch()->getParameter('node')->id();

        if (\Drupal::currentUser()->isAnonymous()) {
            return \Drupal::formBuilder()->getForm('Drupal\transferhub_vote\Form\TransferHubVoteAnonymousForm');
        }
        else
        {
            if (\Drupal\transferhub_vote\Controller\TransferHubVoteController::userAlreadyVoted($userId,$nodeId))
            {
                $currentProjectNid =\Drupal::routeMatch()->getParameter("node")->id();
                $votes = \Drupal\transferhub_vote\Controller\TransferHubVoteController::getVotes($currentProjectNid);
                return array(
                    "type" => "markup",
                    "#markup" => "<h1>".$votes." vote(s)</h1>".t("You already voted for this project")."<br/>".
                        //'<a href="#" onclick="javascript:ga(\'send\', \'event\',\'web\',\'vote\',\'project\',\'projectex\',\'button\'); return;">dispara event</a>'
                        '<p onclick="alert(\'here\');">dispara event</p>'
                );
            }
            else
            {
                return \Drupal::formBuilder()->getForm('Drupal\transferhub_vote\Form\TransferHubVoteAuthenticatedForm');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function blockAccess(AccountInterface $account) {
        return AccessResult::allowedIfHasPermission($account, 'access content');
    }
    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->setConfigurationValue('transferhub_vote_block_settings', $form_state->getValue('transferhub_vote_block_settings'));
    }

}