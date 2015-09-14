<?php

namespace Drupal\renderkit\EntityDisplay\Decorator;

use Drupal\renderkit\EntityDisplay\EntitiesDisplayBase;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;

class NeutralEntityDisplayDecorator extends EntitiesDisplayBase {

  /**
   * @var \Drupal\renderkit\EntityDisplay\EntityDisplayInterface|null
   */
  private $decorated;

  /**
   * Sets the decorated entity display.
   *
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface $decorated
   */
  function __construct(EntityDisplayInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * @param string $entity_type
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  function buildEntities($entity_type, array $entities) {
    return isset($this->decorated)
      ? $this->decorated->buildEntities($entity_type, $entities)
      : array();
  }

}
