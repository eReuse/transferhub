<?php

namespace Drupal\transferhub_devicehub\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \Drupal\transferhub_project\TransferhubProjectTools;
use \Drupal\transferhub_devicehub\Plugin\rest\TransferhubRestServiceTools;

/**
 * Transferhub allocate devices.
 *
 * @RestResource(
 *   id = "transferhub_receive",
 *   label = @Translation("Receive devices for a project"),
 *   uri_paths = {
 *     "canonical" = "/transferhub/events/devices/receive",
 *     "https://www.drupal.org/link-relations/create" = "/transferhub/events/devices/receive"
 *   },
 *  serialization_class = "Drupal\field_collection\Entity\FieldCollection",
 * )
 */
class TransferhubReceiveResource extends ResourceBase {

    /**
     * Responds to POST requests.
     *
     * Returns a watchdog log entry for the specified ID.
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The response containing the log entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *   Thrown when the log entry was not found.
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *   Thrown when no log entry was provided.
     */
    public function post($type, $request) {

        $action = "receive";

        //data received from Request
        $data =  json_decode($request->getContent(), true);

        //get node
        $project_url = $data["project"];
        $url_array = explode("/",$project_url);
        $nid = $url_array[count($url_array)-1];

        //validation
        $valid_states = array("project_workflow_waiting_for_assignment","project_workflow_devices_allocated","project_workflow_devices_received");
        if (!TransferhubRestServiceTools::validRequest($action, $data, $nid, $valid_states, $current_state, $errorResponse))
        {
            return $errorResponse;
        }

        //load node
        $node = \Drupal\node\Entity\Node::load($nid);

        //last update date
        $node->set("field_last_reception",$data["created"]);

        //set devices
        $node->field_received_devices = \Drupal\Core\Field\FieldItemList::createInstance(\Drupal\field_collection\Entity\FieldCollectionItem::class);

        foreach($data["devices"] as $device)
        {
            $did = $device["_id"];
            $dtype = $device["@type"];
            $dsubtype = $device["type"];
            $dmanufacturer = $device["manufacturer"];
            $dmodel = $device["model"];
            $durl = $device["url"];
            $ddonor = $device["donor"]; //this field is not received yet

            $fieldCollectionItem = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_received_devices']);
            $fieldCollectionItem->setHostEntity($node);
            $fieldCollectionItem->set('field_id', $did);
            $fieldCollectionItem->set('field_type', $dtype);
            $fieldCollectionItem->set('field_subtype', $dsubtype);
            $fieldCollectionItem->set('field_manufacturer', $dmanufacturer);
            $fieldCollectionItem->set('field_model', $dmodel);
            $fieldCollectionItem->set('field_url', $durl);
            $fieldCollectionItem->set('field_donor', $ddonor);

            $node->field_received_devices->appendItem(["field_collection_item" => $fieldCollectionItem]);
        }

        //change workflow state
        $next_state = "project_workflow_devices_received";
        TransferhubProjectTools::changeState($node, $next_state, t("Devicehub: @action", array("@action"=> $action)));
       
        //save node
        $node->save();

        //Response & log
        $response["status"] = "OK";
        $response["message"] = t("@n devices received", array("@n" => count($data["devices"])));
        $response["message"] .= " | " . t("Project state changed to: @s",array("@s" => $next_state));

        \Drupal::logger("transferhub_devicehub")->info("REST SERVER: SUCCESS: ".$response["message"]);

        return new ResourceResponse($response, 201);
    }



    /**
     * Responds to GET requests.
     *
     * Returns a watchdog log entry for the specified ID.
     *
     *
     * @return \Drupal\rest\ResourceResponse
     *   The response containing the log entry.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *   Thrown when the log entry was not found.
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *   Thrown when no log entry was provided.
     */
    public function get() {

        $record["status"] = "ok";
        $record["message"] = "RECEIVE you rock";
        if (!empty($record)) {
            return new ResourceResponse($record);
        }
        //throw new BadRequestHttpException(t('Something went wrong'));
        return new ResourceResponse($record);
    }
}
