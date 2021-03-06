<?php

namespace Drupal\renderkit\EntityDisplay\Group;

use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;
use Drupal\renderkit\EntityDisplay\EntitiesDisplayBase;

/**
 * A group of entity display handlers acting as regions in a layout.
 */
class EntityDisplay_Layout extends EntitiesDisplayBase {

  /**
   * @var string
   */
  protected $themeHook;

  /**
   * @var EntityDisplayInterface[]
   */
  protected $regionDisplayHandlers;

  /**
   * @param string $themeHook
   *   The name of a theme hook to render the layout.
   * @param EntityDisplayInterface[] $regionDisplayHandlers
   *   The entity display handlers for each layout region.
   */
  public function __construct($themeHook, array $regionDisplayHandlers) {
    $this->themeHook = $themeHook;
    $this->regionDisplayHandlers = $regionDisplayHandlers;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  public function buildEntities($entityType, array $entities) {
    $builds = [];
    foreach ($this->regionDisplayHandlers as $name => $handler) {
      foreach ($handler->buildEntities($entityType, $entities) as $delta => $entity_build) {
        $builds[$delta][$name] = $entity_build;
      }
    }
    foreach ($builds as $delta => $entity_builds) {
      $builds[$delta]['#theme'] = $this->themeHook;
    }
    return $builds;
  }
}
