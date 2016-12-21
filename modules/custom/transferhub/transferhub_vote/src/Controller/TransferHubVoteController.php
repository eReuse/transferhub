<?php



namespace Drupal\transferhub_vote\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;


/**
 * Controller
 */
class TransferHubVoteController extends ControllerBase {


    public function login()
    {
        return new \Symfony\Component\HttpFoundation\RedirectResponse(  base_path() . "user/simple-fb-connect");
    }

    public function redirect()
    {
        $returnUrl = $_SESSION['transferhub_vote']['returnUrl'];

        if (isset($returnUrl) && !empty($returnUrl)) {
            //comes from Voting Block
            return new \Symfony\Component\HttpFoundation\RedirectResponse($returnUrl);
        }
        else
            //comes from login page
            return new \Symfony\Component\HttpFoundation\RedirectResponse("/");
    }

    public function vote($projectNid)
    {
        $returnUrl = $_SESSION['transferhub_vote']['returnUrl'];

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

