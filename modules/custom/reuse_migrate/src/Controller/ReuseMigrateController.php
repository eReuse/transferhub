<?php

/**
 * @file
 * Contains \Drupal\loremipsum\Controller\LoremIpsumController
 */

namespace Drupal\reuse_migrate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
// Change following https://www.drupal.org/node/2457593
use Drupal\Component\Utility\SafeMarkup;
//use Drupal\Core\Database;



/**
 * Controller routines for Lorem ipsum pages.
 */
class ReuseMigrateController extends ControllerBase {



        public function projects()
        {
            //\Drupal\Core\Database\Database::setActiveConnection("intermediateD8");

           /*$query = QueryProjects::create(new \Symfony\Component\DependencyInjection\ContinerInerface());

            $nids = $query->getProjects();

            drupal_set_message("total nodes a origen: ".count($nids));*/

            /*foreach ($nodes_origen as $node) {
                drupal_set_message($node->get("nid"));
                }*/

            //$nodes_origen = \Drupal\node\Entity\Node::loadMultiple();

            //\Drupal\Core\Database\Database::setActiveConnection();

            /*foreach($nodes_origen as $node)
            {
                $n = \Drupal\node\Entity\Node::load($node->get("nid"));

                if (!$n && $node->get("nid") == "6621")
                {
                    $new_node = \Drupal\node\Entity\Node::create();
                    $new_node = $node;
                    $new_node->save();

                }
            }*/

            return array(
                '#type' => 'markup',
                '#markup' => $this->t("something will be done here"),
            );
        }
        public function photos()
        {
            $node = \Drupal\node\Entity\Node::load(6616);

            drupal_set_message($node->field_image_dp->entity->url());

            //TODO file entity
            //$node->set("field_image_dp","hola");
            //$node->save();

            //drupal_set_message(print_r($node->field_image_dp->entity,true));
            //$image = $node->get("field_image_dp");

            //var_dump($image[0]);
            //drupal_set_message($image[0]);

            return array(
                '#type' => 'markup',
                '#markup' => $this->t("something will be done here"),
            );
        }

        public function equipment() {

        // Default settings
        /*$config = \Drupal::config('reuse_migrate.settings');
        // Page title and source text.
        $host = $config->get('reuse_migrate.origin_db_host');
        $user = $config->get('reuse_migrate.origin_db_user');
        $psswd = $config->get('reuse_migrate.origin_db_psswd');
        $db = $config->get('reuse_migrate.origin_db_db');
        $port = $config->get('reuse_migrate.origin_db_port');
        if (!isset($port) || empty($port))
            $port = '3306';*/

        //RETRIVE DATA FROM ORIGIN
        \Drupal\Core\Database\Database::setActiveConnection("drupal6");
        $db = \Drupal\Core\Database\Database::getConnection();
        $data = $db->select('xsr_equipment_initiative_equipments_needed','t')->fields('t')->execute();
        $equips = array();
        while ($row = $data->fetchObject())
        {
            $equips[$row->nid] = unserialize($row->equipments);
        }
        //echo "<pre>".print_r($equips, true)."</pre>";

        //UPDATE DATA
        \Drupal\Core\Database\Database::setActiveConnection();
        $count = 0;
        foreach ($equips as $nid => $valors)
        {
            if  (is_numeric($nid) && count($valors) > 0)
            {
                $node = \Drupal\node\Entity\Node::load($nid);
                if ($node)
                {
                    $debug = "";
                    foreach ($valors as $id => $quantitat)
                    {
                        $field = null;
                        switch($id)
                        {
                            case 1: //desktop
                                $field = "field_equips_desktop"; break;
                            case 2: //portàtil
                                $field = "field_equips_portatil"; break;
                            case 5: //server
                                $field = "field_equips_servidor";break;
                            case 501: //smartphone
                                $field = "field_equips_smartphone"; break;
                            case 502: //tablets
                                $field = "field_equips_tablet";break;
                            case 203: //impresora
                                $field = "field_equips_impresora"; break;
                            case 209: //teclat
                                $field = "field_equips_teclat";break;
                            case 210: //ratolí
                                $field = "field_equips_ratoli"; break;
                            case 102: //monitor
                                $field = "field_equips_monitor"; break;
                        }
                        if ($field) {
                            $node->set($field, $quantitat);
                            $debug .= $field.": ".$quantitat.", ";
                        }
                    }
                    $node->save();
                    $count++;
                    drupal_set_message($nid."=>".$debug);
                }
            }
        }

        return array(
            '#type' => 'markup',
            '#markup' => $this->t($count." nodes updated"),
        );
    }

}