<?php

namespace Drupal\renderkit\EntityDisplay\EntityImageDerivative;

use Drupal\renderkit\EntityDisplay\EntityDisplayMultipleBase;
use Drupal\renderkit\EntityImage\EntityImageInterface;

/**
 * A decorator class for entity image.
 *
 * Unlike typical "true" decorators, the decorated object is not injected in the
 * constructor, but with a decorate() method. This allows the instance to exist
 * without a decorated object, which allows for a decorator / processor duality
 * in inheriting classes.
 *
 * @see \Drupal\renderkit\EntityDisplay\Wrapper\NeutralEntityWrapper
 */
class NeutralEntityImageDerivative extends EntityDisplayMultipleBase {

  /**
   * The decorated image provider.
   *
   * @var \Drupal\renderkit\EntityImage\EntityImageInterface
   */
  private $imageProvider;

  /**
   * Sets the decorated entity display providing the image from each entity.
   *
   * @param \Drupal\renderkit\EntityImage\EntityImageInterface $imageProvider
   *   Object that extracts an image path from an entity, e.g. based on an image
   *   field, or the user picture, etc.
   *
   * @return $this
   */
  function decorate(EntityImageInterface $imageProvider) {
    $this->imageProvider = $imageProvider;

    return $this;
  }

  /**
   * @param string $entity_type
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   * @throws \Exception
   */
  function buildMultiple($entity_type, array $entities) {
    if (empty($this->imageProvider)) {
      return array();
    }
    $builds = $this->imageProvider->buildMultiple($entity_type, $entities);
    foreach ($builds as $delta => $build) {
      if (empty($build)) {
        unset($builds[$delta]);
      }
      elseif (!isset($build['#theme']) || 'image' !== $build['#theme']) {
        throw new \Exception("Render arrays from image providers must use '#theme' => 'image'.");
      }
    }
    return $builds;
  }
}
