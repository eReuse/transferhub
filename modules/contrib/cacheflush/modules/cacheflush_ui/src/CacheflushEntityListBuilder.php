<?php

/**
 * @file
 * Contains Drupal\cacheflush_ui\CacheflushEntityListBuilder.
 */

namespace Drupal\cacheflush_ui;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;

/**
 * Defines a class to build a listing of Cacheflush entity entities.
 *
 * @ingroup cacheflush_ui
 */
class CacheflushEntityListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Cacheflush entity ID - List Builder');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\cacheflush\Entity\CacheflushEntity */
    $row['id'] = $entity->id();
    return $row + parent::buildRow($entity);
  }

}
