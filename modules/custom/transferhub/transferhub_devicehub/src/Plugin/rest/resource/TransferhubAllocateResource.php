<?php

namespace Drupal\transferhub_devicehub\Plugin\rest\resource;


use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\workflow\Entity\Workflow;
use \Drupal\workflow\Entity\WorkflowTransition;
use \Drupal\transferhub_project\TransferhubProjectTools;
use \Drupal\transferhub_devicehub\Plugin\rest\TransferhubRestServiceTools;


/**
 * Transferhub allocate devices.
 *
 * @RestResource(
 *   id = "transferhub_allocate",
 *   label = @Translation("Allocate devices for a project"),
 *   uri_paths = {
 *     "canonical" = "/transferhub/events/devices/allocate",
 *     "https://www.drupal.org/link-relations/create" = "/transferhub/events/devices/allocate"
 *   },
 *  serialization_class = "Drupal\field_collection\Entity\FieldCollection",
 * )
 */
class TransferhubAllocateResource extends ResourceBase {

    /**
     * Responds to POST requests.
     *
     *
     */
    public function post($type, $request) {         

        $action = "allocate";
        
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
        $node->set("field_last_allocation",$data["created"]);

        //set devices
        $node->field_allocated_devices = \Drupal\Core\Field\FieldItemList::createInstance(\Drupal\field_collection\Entity\FieldCollectionItem::class);

        foreach($data["devices"] as $device)
        {
            $did = $device["_id"];
            $dtype = $device["@type"];
            $dsubtype = $device["type"];
            $dmanufacturer = $device["manufacturer"];
            $dmodel = $device["model"];
            $durl = $device["url"];            

            $fieldCollectionItem = \Drupal\field_collection\Entity\FieldCollectionItem::create(['field_name' => 'field_allocated_devices']);
            $fieldCollectionItem->setHostEntity($node);
            $fieldCollectionItem->set('field_id', $did);
            $fieldCollectionItem->set('field_type', $dtype);
            $fieldCollectionItem->set('field_subtype', $dsubtype);
            $fieldCollectionItem->set('field_manufacturer', $dmanufacturer);
            $fieldCollectionItem->set('field_model', $dmodel);
            $fieldCollectionItem->set('field_url', $durl);

            $node->field_allocated_devices->appendItem(["field_collection_item" => $fieldCollectionItem]);
        }
               
        //change workflow state
        $next_state = "project_workflow_devices_allocated";                  
        TransferhubProjectTools::changeState($node, $next_state, t("Devicehub: @action", array("@action"=> $action)));
        
        //save node
        $node->save();

        //Response & log
        $response["status"] = "OK";
        $response["message"] = t("@n devices allocated", array("@n" => count($data["devices"])));
        $response["message"] .= " | " . t("Project state changed to: @s",array("@s" => $next_state));

        \Drupal::logger("transferhub_devicehub")->info("REST SERVER: SUCCESS: ".$response["message"]);
        
        return new ResourceResponse($response, 201);
    }
}