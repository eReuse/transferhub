<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */

namespace Drupal\hello\Controller;

use Drupal\Core\Controller\ControllerBase;

class HelloController extends ControllerBase {
    public function content() {
        return array(
            '#type' => 'markup',
            '#markup' => $this->t('Hello, World!'),
        );
    }
}