<?php

/**
 * @file
 * Contains \Drupal\custom_pub\Plugin\RulesAction\SetCustomPublishingOption.
 */

namespace Drupal\transferhub_project\Plugin\RulesAction;

//use Drupal\custom_pub\CustomPublishingOptionInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\transferhub_project\TransferhubProjectTools;

/**
 * Provides an action to trigger a custom publishing option.
 *
 * @RulesAction(
 *   id = "rules_update_publication_status",
 *   label = @Translation("Publish/unpublish project based on workflow status"),
 *   category = @Translation("Content"),
 *   context = {
 *    "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       description = @Translation("Specifies the entity")
 *     ),
 *   }
 * )
 */
class UpdateProjectPublication extends RulesActionBase {

    /**
     * Sets the custom publishing option on a given entity.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to be saved.
     */
    protected function doExecute() { //EntityInterface $entity

        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node) {
           TransferhubProjectTools::updatePublication($node);
        }
    }
}
