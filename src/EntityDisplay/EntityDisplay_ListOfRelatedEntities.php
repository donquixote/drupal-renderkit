<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface;
use Drupal\renderkit\EntityToEntities\EntityToEntitiesInterface;

/**
 * @CfrPlugin("listOfRelatedEntities", "Related entities")
 */
class EntityDisplay_ListOfRelatedEntities extends EntitiesDisplayBase {

  /**
   * @var \Drupal\renderkit\EntityToEntities\EntityToEntitiesInterface
   */
  private $entityToEntities;

  /**
   * @var \Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface
   */
  private $entitiesListFormat;

  /**
   * @param \Drupal\renderkit\EntityToEntities\EntityToEntitiesInterface $entityToEntities
   * @param \Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface $entitiesListFormat
   */
  public function __construct(EntityToEntitiesInterface $entityToEntities, EntitiesListFormatInterface $entitiesListFormat) {
    $this->entityToEntities = $entityToEntities;
    $this->entitiesListFormat = $entitiesListFormat;
  }

  /**
   * Builds render arrays from the entities provided.
   *
   * Both the entities and the resulting render arrays are in plural, to allow
   * for more performant implementations.
   *
   * Array keys and their order must be preserved, although implementations
   * might remove some keys that are empty.
   *
   * @param string $entityType
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *   The array keys can be anything, they don't need to be the entity ids.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  public function buildEntities($entityType, array $entities) {

    $targetEntitiess = $this->entityToEntities->entitiesGetRelated($entityType, $entities);

    $builds = [];
    foreach ($targetEntitiess as $delta => $targetEntities) {
      if (!is_array($targetEntities)) {
        dpm(get_defined_vars());
        break;
      }
      $builds[$delta] = $this->entitiesListFormat->entitiesBuildList($entityType, $targetEntities);
    }

    return $builds;
  }
}
