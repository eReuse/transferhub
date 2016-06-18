<?php

/**
 * @file
 * Contains \Drupal\loremipsum\Controller\LoremIpsumController
 */

namespace Drupal\transferhub_vote\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;
//use Drupal\Core\Database;



/**
 * Controller routines for Lorem ipsum pages.
 */
class TransferHubVoteController extends ControllerBase {


    public function login()
    {
        return new \Symfony\Component\HttpFoundation\RedirectResponse("/reutilitza/user/simple-fb-connect");
        /* return array(
             '#type' => 'markup',
             '#markup' => $this->t("something will be done here"),
         );*/
    }

    public function redirect()
    {
        $returnUrl = $_SESSION['transferhub_vote']['returnUrl'];

        return new \Symfony\Component\HttpFoundation\RedirectResponse($returnUrl);
    }

    public function vote($projectNid)
    {
        $returnUrl = $_SESSION['transferhub_vote']['returnUrl'];
       // $nid = $_SESSION['transferhub_vote']['node'];

        if ($projectNid && \Drupal::currentUser()->isAuthenticated())
        {
            ;
            $user = \Drupal::currentUser();
            if (!self::userAlreadyVoted($user->id(), $projectNid))
            {
                self::registerVote($user->id(),$projectNid);
            }
        } 

        return new \Symfony\Component\HttpFoundation\RedirectResponse($returnUrl);
        /*return array(
            '#type' => 'markup',
            '#markup' => "return url: ".$returnUrl,
        );*/
    }



    public static function userAlreadyVoted($userId, $nodeId)
    {
        $db = \Drupal\Core\Database\Database::getConnection();
        $data = $db->select('transferhub_votes','v')->fields("v")->execute();

        $found = false;
        while ($row = $data->fetchObject())
        {
            if ($row->nid == $nodeId && $row->uid == $userId)
            {
                $found = true;
                break;
            }
        }
        return $found;
    }

    public static function registerVote($userId, $nodeId)
    {

        $db = \Drupal\Core\Database\Database::getConnection();
        $db->insert("transferhub_votes")->fields(array("uid","nid"),array($userId,$nodeId))->execute();
    }

    public static function getVotes($nodeId)
    {
        $db = \Drupal\Core\Database\Database::getConnection();
        $data = $db->select('transferhub_votes','v')->fields("v")->execute();
        $votes = 0;
        while ($row = $data->fetchObject())
        {
            if ($row->nid == $nodeId )
            {
                $votes++;
            }
        }
        return $votes;
    }
}

