<?php
/**
 * Created by PhpStorm.
 * User: albert
 * Date: 15/08/16
 * Time: 18:38
 */

namespace Drupal\transferhub_project;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\workflow\Entity\Workflow;
use \Drupal\workflow\Entity\WorkflowTransition;

class TransferhubProjectTools {

    static function loadAndChangeState($nid, $next_state, $comment)
    {
        $node = \Drupal\node\Entity\Node::load($nid);

        if(!$node || $node->getType() != "project")
            return false;

        return TransferhubProjectTools::changeState($node, $next_state, $comment, true);
    }

    static function changeState(\Drupal\node\Entity\Node &$node, $next_state, $comment, $forceSave = false)
    {
        if ($node->getType() != "project")
            return false;

        $current_state = $node->get("field_workflow")->getValue()[0]["value"];

        //execute transition (save in history)
        $transition =  WorkflowTransition::create([$current_state]);
        $transition->setValues($next_state, $uid = NULL, $timestamp = REQUEST_TIME, $comment);
        $transition->setTargetEntity($node);
        $transition->execute(true);

        //assign node state
        $node->get("field_workflow")->setValue($next_state);

        //force save? if false, save should be executed in the caller function
        if ($forceSave)
            $node->save();

        return true;
    }
    
    static function updatePublication(\Drupal\node\Entity\Node &$node)
    {
        if ($node->getType() != "project") 
            return false;
        
        $workflow = $node->get("field_workflow")->getValue();
        $state = $workflow[0]["value"];
        
        switch ($state)
        {
            case  "project_workflow_waiting_for_assignment": { }
            case  "project_workflow_devices_allocated": { }
            case  "project_workflow_devices_received": { }
            case  "project_workflow_finished": {
                //publish content
                $node->setPublished(true);
                $message = t("published after changing to state: ");
                break;
            }
            case "project_workflow_creation": {}
            case "project_workflow_draft": {}
            case "project_workflow_rejected": {}
            case "project_workflow_cancelled": {}
            default:
            {
                //unpublish content
                $node->setPublished(false);
                $message = t("unpublished after changing to state: ");
                break;
            }
        }
        $node->save();

        //LOG
        \Drupal::logger("transferhub_project")->info("Project ". $node->id() . ": " . $message . $state);
        return true;
    }
}