<?php
/**
 * Created by PhpStorm.
 * User: albert
 * Date: 16/05/16
 * Time: 23:54
 */

namespace Drupal\reuse_migrate\Controller;


class QueryProjects{

    public function __construct(\Drupal\Core\Entity\Query\QueryFactory $entity_query) {
        $this->entity_query = $entity_query;
    }

    public static function create(\Symfony\Component\DependencyInjection\ContinerInerface $container) {
    return new static(
    // User the $container to get a query factory object. This object let's us create query objects.
        $container->get('entity.query')
    );
    }

    public function getProjects()
    {
        $query = $this->entity_query->get('node');

        // Add a filter (published).
        $query->condition('type','iniciativa');

        // Run the query.
        $nids = $query->execute();

        return $nids;
    }


}