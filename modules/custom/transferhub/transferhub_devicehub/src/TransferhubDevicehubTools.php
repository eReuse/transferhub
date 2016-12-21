<?php
/**
 * Created by PhpStorm.
 * User: albert
 * Date: 25/08/16
 * Time: 00:21
 */
namespace Drupal\transferhub_devicehub;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\workflow\Entity\Workflow;
use \Drupal\workflow\Entity\WorkflowTransition;

class TransferhubDevicehubTools {
    
    static function _getBaseURL()
    {
        $base_url = "http://" . \Drupal::request()->getHost() . base_path();
        if (strpos(\Drupal::request()->getHost(), "localhost") !== false)
        {
            //todo
            $base_url = "http://reutilitza.dev/";
        }
        return $base_url;
    }
    
    static function _debug()
    {
       if (strpos(\Drupal::request()->getHost(), "localhost") !== false  //local
           || strpos(\Drupal::request()->getHost(), ".dev") !== false //local
           || strpos(\Drupal::request()->getHost(), "transferhub.ereuse.net") !== false) //Test Server

           return true;
        else
            return false;
    }
    
    static function syncronizeProjectState(\Drupal\node\Entity\Node $node)
    {
        if ($node->getType() != "project")
            return false;
        
        $workflow = $node->get("field_workflow")->getValue();        
        $state = $workflow[0]["value"];

        $base_url = self::_getBaseURL();

        $projectUrl = $base_url . "node/".$node->id();
        $eventDescription = t("State changed in Transferhub");
        $date = gmdate("c");
        $date = explode("+" , $date)[0];
        $userUrl = $base_url . "user/" . \Drupal::currentUser()->id();

        switch ($state)
        {
            case  "project_workflow_rejected":{
                //Reject project
                $event = "reject";
                break;
            }
            case  "project_workflow_waiting_for_assignment": {
                //Accept project
                $event = "accept";
                break;
            }
            case  "project_workflow_cancelled": {
                //Cancel project
                $event = "cancel";
                break;
            }
            case  "project_workflow_finished": {
                //Finish project
                $event = "finish";
                break;
            }
        }

        $api = new \Drupal\transferhub_devicehub\TransferhubDevicehubRestClient(self::_debug());
        if (isset($event))
        {
            $result = $api->projectEvent($event, $projectUrl, null, $eventDescription, $date, $userUrl);
            
            if ($result) {
                //LOG
                //drupal_set_message("Project " . $node->id() . " changed to status " . $state); todo
                \Drupal::logger("transferhub_devicehub")->info("Project " . $node->id() . ": " . t("wokflow state syncronized to Devicehub") . " -> " . $state);
                return true;
            }
            return false;            
        }      
        else
            return false;
    }

    static function createUser($account, $password) {

        $base_url = self::_getBaseURL();

        $role = null;
        if (array_intersect(["manager","administrator"],array_values($account->getRoles())))
        {
            $role = "admin";
        }
            
        $user_url = $base_url. "user/".$account->id();

        $api = new  \Drupal\transferhub_devicehub\TransferhubDevicehubRestClient(self::_debug());
        
        $api->createUser( $user_url, $account->getEmail(), $password, $role);
    }
    
    static function createProject(\Drupal\node\Entity\Node &$node) {

        $base_url = self::_getBaseURL();

        //basic information
        $title = $node->getTitle();
        $desc = str_replace(array("\r", "\n"),"",strip_tags($node->get("field_description")->getValue()[0]["value"]));
        $shortDesc = str_replace(array("\r", "\n"),"",strip_tags($node->get("field_short_description")->getValue()[0]["value"]));
        $url = $base_url . "node/".$node->id();
        foreach($node->get("field_tags") as $term)
        {
            $term_id = $term->toArray()["target_id"];
            $term_name = \Drupal\taxonomy\Entity\Term::load($term_id)->toArray()["name"][0]["value"];
            $tags[] = $term_name;
        }
        $date = $node->get("field_deadline")->getValue()[0]["value"];
        if (isset($date) && !empty($date))
            $deadline = $date . "T00:00:00";

        $author_url = $base_url . "user/" . $node->get("uid")->getValue()[0]["target_id"];

        if (\Drupal::moduleHandler()->moduleExists('transferhub_vote'))
        {
            $votes = \Drupal\transferhub_vote\Controller\TransferHubVoteController::getVotes($node->id());
        }
        else
            $votes = 0;

        $image_id = $node->get("field_image")->getValue()[0]["target_id"];
        $image = file_create_url(\Drupal\file\Entity\File::load($image_id)->getFileUri());
        $image = str_replace("localhost/reutilitza","www.reutilitza.cat", $image); //todo ESBORRAR

        //social
        $link_web = $node->get("field_website")->getValue()[0]["uri"];
        $link_fb = $node->get("field_facebook")->getValue()[0]["uri"];
        $link_twitter = $node->get("field_twitter")->getValue()[0]["uri"];

        //requipred equipment
        $count_desktop = $node->get("field_desktop")->getValue()[0]["value"];
        $count_desktop_peripherals = $node->get("field_desktop_with_peripherals")->getValue()[0]["value"];
        $count_laptop = $node->get("field_laptop")->getValue()[0]["value"];
        $count_phone = $node->get("field_mobile_phone")->getValue()[0]["value"];
        $count_tablet = $node->get("field_tablet_computer")->getValue()[0]["value"];
        $count_monitor = $node->get("field_computer_monitor")->getValue()[0]["value"];

        //address
        $address = $node->get("field_address")->getValue()[0];
        $addr_country = $address["country_code"];
        $addr_locality = $address["locality"];
        $addr_region = $address["administrative_area"];
        $addr_zip = $address["postal_code"];
        $addr_street = $address["address_line1"];

        //call DeviceHub
        $api = new  \Drupal\transferhub_devicehub\TransferhubDevicehubRestClient(self::_debug());
        $api->createProject(
            //basic information
            $title, $desc, $shortDesc, $url, $tags, $deadline, $author_url, $image,
            //social
            $link_web, $link_fb, $link_twitter, $votes,
            //required equipment
            $count_desktop, $count_desktop_peripherals, $count_laptop, $count_phone, $count_tablet, $count_monitor,
            //address
            $addr_country, $addr_locality, $addr_region, $addr_zip, $addr_street);
    }
}