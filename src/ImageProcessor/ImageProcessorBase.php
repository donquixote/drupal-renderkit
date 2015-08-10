<?php

namespace Drupal\renderkit\ImageProcessor;

use Drupal\renderkit\EntityDisplay\EntityImageDecorator\NeutralEntityImageDecorator;

abstract class ImageProcessorBase extends NeutralEntityImageDecorator implements ImageProcessorInterface {

  /**
   * @param string $entity_type
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  function buildMultiple($entity_type, array $entities) {
    $builds = parent::buildMultiple($entity_type, $entities);
    foreach ($builds as &$build) {
      $build = $this->processImage($build);
    }
    return $builds;
  }

}