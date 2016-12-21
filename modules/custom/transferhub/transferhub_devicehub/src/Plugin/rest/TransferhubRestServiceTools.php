<?php
/**
 * Created by PhpStorm.
 * User: albert
 * Date: 15/08/16
 * Time: 15:49
 */

namespace Drupal\transferhub_devicehub\Plugin\rest;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\workflow\Entity\Workflow;
use \Drupal\workflow\Entity\WorkflowTransition;

class TransferhubRestServiceTools {

    static function raiseError($message, $params = NULL, $status = 422)
    {
        $response = array();
        $response['status'] = '_error';
        if ($params)
            $response["error"] = t($message, $params);
        else
            $response["error"] = t($message);
        
        \Drupal::logger("transferhub_devicehub")->error("REST SERVER: ERROR: ".$response["error"]." | status: ".$status);
        return new ResourceResponse($response, $status);
    }
    
    static function validRequest($action, $data, $nid, $valid_states, &$current_state, &$response)
    {
        $valid = false;

        //load node
        $node = \Drupal\node\Entity\Node::load($nid);
        
        //validate node type        
        if (!$node || $node->getType() != "project")
        {
            $response = TransferhubRestServiceTools::raiseError("Project with id @nid does not exist",array("@nid" => $nid),422);
            return false;
        }
        //validate workflow state
        $current_state =  $node->get("field_workflow")->getValue()[0]["value"];
        if (!isset($current_state) || empty($current_state))
            $current_state= "project_workflow_draft";

        if (!in_array($current_state,$valid_states))
        {
            $response = TransferhubRestServiceTools::raiseError("Cannot @action devices because project is in state @state", array("@state" => $current_state, "@action" => $action), 422);
            return false;
        }
        //request contains devices?
        if (!$data["devices"] || count($data["devices"]) == 0) {

            $response =  TransferhubRestServiceTools::raiseError("No devices sent", NULL , 422);
            return false;
        }
        
        return true;
    }

}