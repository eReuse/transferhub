<?php

/**
 * @file
 * Contains \Drupal\loremipsum\Controller\LoremIpsumController
 */

namespace Drupal\transferhub_devicehub\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;
//use Drupal\Core\Database;
use Drupal\workflow\Entity\WorkflowTransition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use \Drupal\workflow\Entity\Workflow;

/**
 * Controller routines for Lorem ipsum pages.
 */
class TransferHubDeviceHubController extends ControllerBase {

    public function apiTest() //todo delete
    {
        $client = new \Drupal\transferhub_devicehub\transferhub_DeviceHubRestClient();

        $result = $client->login();
        echo "<pre>";
        echo print_r($result,true);
        echo "</pre>";
        die;

        //GET
        //test server: ttp://jsonplaceholder.typicode.com
        /*echo "GET<br/>";
        $result = $client->call("posts/3", "GET");
        var_dump($result);

        echo "<br/>POST<br/>";
        $result = $client->call("posts", "POST", array("title" => "You rock"));
        var_dump($result);

        die;*/

        $return = array(
          "#type" => "markup",
            "#markup" => print_r($result, true)
        );
        return $return;
    }

    public function eventTest()
    {
        $nid = 6648;
        $node = \Drupal\node\Entity\Node::load($nid);

        $iterator = 0;
        //kint($node->field_allocated_devices->get("field_collection_item"));
        kint($node->field_allocated_devices->getValue());

        for ($iterator = 0; $iterator < $node->field_allocated_devices->count(); $iterator++)
        {
            kint($node->field_allocated_devices->get($iterator));
            //$item = $node->field_allocated_devices->get($iterator);
            //kint($node->field_allocated_devices->get($iterator)->get("field_id"));
            //kint($item->getValue("field_id"));
        }


        /*
        $api = new \Drupal\transferhub_devicehub\transferhub_DeviceHubRestClient();

        $base_url = "http://" . \Drupal::request()->getHost() . base_path();
        if (strpos(\Drupal::request()->getHost(), "localhost") !== false)
        {
            //todo
            $base_url = "http://www.reutilitza.cat/";
        }

        $url = $base_url . "node/".$node->id();

        $content["@type"] = "projects:Accept";
        $content["date"] = date("Y-d-m") . "T00:00:00";
        $content["description"] = "Sent from transferhub";
        $content["byUser"] = $base_url . "user/" . \Drupal::currentUser()->id();
        $content["project"] = $url;
        $content["url"] = $url;

        $url = "db1/events/projects/accept";
        $result = $api->call($url,"POST",$content);

        $return = array(
            "#type" => "markup",
            "#markup" => print_r($result, true)
        );
        return $return;*/

    }

    public function projectTest() //todo delete
    {

        /*
         $nid = 6628;
        $node = \Drupal\node\Entity\Node::load($nid);

        //record transition (history)
        $transition =  WorkflowTransition::create(["project_workflow_waiting_for_assignment"]);
        $transition->setValues("project_workflow_draft", $uid = NULL, $timestamp = REQUEST_TIME, "Devicehub: 4");
        $transition->setTargetEntity($node);
        $result["sortida"] = $transition->execute(true);

        //change node state
        $node->get("field_workflow")->setValue("project_workflow_draft");
        $node->save();
        */

        $nid = 6648;
        $node = \Drupal\node\Entity\Node::load($nid);
         //kint($node); die;
        
        $api = new  \Drupal\transferhub_devicehub\transferhub_DeviceHubRestClient();

        $base_url = "http://" . \Drupal::request()->getHost() . base_path();
        if (strpos(\Drupal::request()->getHost(), "localhost") !== false)
        {
            //todo
            $base_url = "http://www.reutilitza.cat/";
        }

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
        $author_url = $base_url . "user/77" ; //todo treure

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
        $result = $api->createProject(
            //basic information
            $title, $desc, $shortDesc, $url, $tags, $deadline, $author_url, $image,
            //social
            $link_web, $link_fb, $link_twitter, $votes,
            //required equipment
            $count_desktop, $count_desktop_peripherals, $count_laptop, $count_phone, $count_tablet, $count_monitor,
            //address
            $addr_country, $addr_locality, $addr_region, $addr_zip, $addr_street);

        $return = array(
            "#type" => "markup",
            "#markup" => print_r($result, true)
        );
        return $return;
    }

    private function validateToken($token) //todo delete
    {
        if (!isset($token) || !empty($token))
            return false;
        else if ($token != "Basic reutilitza")
            return false;
        else
            return true;
    }

    public function allocateDevices(Request $request) //todo esborrar
    {
        /*drupal_set_message("Devices allocated");
        return array(
            '#type' => 'markup',
            '#markup' => $this->t("something will be done here"),
        );*/

        // This condition checks the `Content-type` and makes sure to
        // decode JSON string from the request body into array.
        if ( 0 === strpos( $request->headers->get( 'Content-Type' ), 'application/json' ) ) {


            if (!$this->validateToken($request->headers->get( 'Authorization' )))
                return new JsonResponse( NULL, 403);

            $response['status'] = 'OK';
            $data = json_decode( $request->getContent(), TRUE );
            $request->request->replace( is_array( $data ) ? $data : [] );

            $id = $data["id"];
            $url = $data["url"];
            $nid = $data["project"];
            $type = $data["@type"];
            $count = 0; //total number of devices

            //node
            $node = \Drupal\node\Entity\Node::load($nid);
            //workflow state
            if ($node)
                $state =  $node->get("field_workflow")->getValue()[0]["value"];

            if ($type != "Allocate")
            {
                $response['status'] = 'ERROR';
                $response["error_msg"] = t("@type field must be `Allocate`");

            }elseif (count($data["devices"]) == 0)
            {
                $response['status'] = 'ERROR';
                $response["error_msg"] = t("No devices sent");

            }elseif (!$node)
            {
                $response['status'] = 'ERROR';
                $response["error_msg"] = t("Project with id @nid does not exist", array("@nid" => $nid));

            }elseif ($state != "project_workflow_waiting_for_assignment")
            {
                $response['status'] = 'ERROR';
                $response["error_msg"] = t("Cannot allocate devices because project is in state @state", array("@state" => $state));
            }
            else
            {
                //replace all devices each time
                $node->field_allocated_devices = \Drupal\Core\Field\FieldItemList::createInstance(\Drupal\field_collection\Entity\FieldCollectionItem::class);

                $count = count($data["devices"]); //count devices
                foreach($data["devices"] as $device)
                {
                    $did = $device["_id"];
                    $dlabel = $device["label"];
                    $durl = $device["url"];

                    $fieldCollectionItem = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_allocated_devices']);
                    $fieldCollectionItem->setHostEntity($node);
                    $fieldCollectionItem->set('field_id', $did);
                    $fieldCollectionItem->set('field_label', $dlabel);
                    $fieldCollectionItem->set('field_url', $durl);

                    $node->field_allocated_devices->appendItem(["field_collection_item" => $fieldCollectionItem]);
                }
                //save node
                $node->save();
            }
        }
        else {
            $response['status'] = 'ERROR';
            $response["error_msg"] = t("Data in request is not JSON");
        }

        //LOG REQUEST
        \Drupal::logger('transferhub_devicehub')->info("DEVICE ALLOCATION: number of allocated devices: ".$count."| id: ".$id."| url: ".$url."| project: ".$nid."| type: ".$type);
        if ($response["status"] != "OK")
        {
            \Drupal::logger('transferhub_devicehub')->error("DEVICE ALLOCATION: communication error: ".$response["error_msg"]);
        }

        return new JsonResponse( $response );
    }
}

