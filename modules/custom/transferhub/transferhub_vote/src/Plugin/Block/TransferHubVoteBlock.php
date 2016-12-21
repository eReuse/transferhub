<?php

namespace Drupal\transferhub_vote\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 *
 * @Block(
 *   id = "transferhub_vote_block",
 *   admin_label = @Translation("TransferHub Voting block"),
 * )
 */

class TransferHubVoteBlock extends BlockBase {

    /**
     * {@inheritdoc}
     */
    public function build() {
        
        $userId = \Drupal::currentUser()->id();
        $nodeId = \Drupal::routeMatch()->getParameter('node')->id();
        //drupal_set_message($nodeId);        
        
        $votes = \Drupal\transferhub_vote\Controller\TransferHubVoteController::getVotes($nodeId);
        
        $build_array = array(
            "#theme" => "transferhub_vote_block",
            "#votes" => $votes,
            '#cache' => [
                'max-age' => 0,
            ]
        );      

        if (\Drupal::currentUser()->isAnonymous()) { 
            $build_array["#form"] = \Drupal::formBuilder()->getForm('Drupal\transferhub_vote\Form\TransferHubVoteAnonymousForm');
        }
        else
        {
            if (\Drupal\transferhub_vote\Controller\TransferHubVoteController::userAlreadyVoted($userId,$nodeId))
            {
                $build_array["#voted"] = true;
            }
            else
            {
                $build_array["#form"] = \Drupal::formBuilder()->getForm('Drupal\transferhub_vote\Form\TransferHubVoteAuthenticatedForm');
            }
        }

        $node = \Drupal\node\Entity\Node::load($nodeId);
        
        //Required devices
        $build_array["#required_devices"] = $node->get("field_desktop")->getValue()[0]["value"]
            + $node->get("field_desktop_with_peripherals")->getValue()[0]["value"]
            + $node->get("field_laptop")->getValue()[0]["value"]
            + $node->get("field_mobile_phone")->getValue()[0]["value"]
            + $node->get("field_tablet_computer")->getValue()[0]["value"]
            + $node->get("field_computer_monitor")->getValue()[0]["value"];

        //kint($node->get("field_deadline")->getvalue()[0]["value"]);
        
        $build_array["#deadline"] = $node->get("field_deadline")->getvalue()[0]["value"];
        
        return $build_array;
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