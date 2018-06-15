<?php

namespace Drupal\renderkit\EntityDisplay\Decorator;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\EntityDisplay\EntityDisplay;
use Drupal\renderkit\EntityDisplay\EntityDisplayInterface;
use Drupal\renderkit\EntityToEntity\EntityToEntity;
use Drupal\renderkit\EntityToEntity\EntityToEntityInterface;

class EntityDisplay_RelatedEntity implements EntityDisplayInterface {

  /**
   * @var \Drupal\renderkit\EntityToEntity\EntityToEntityInterface
   */
  private $entityToEntity;

  /**
   * @var \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  private $relatedEntityDisplay;

  /**
   * @var string
   */
  private $relatedEntityType;

  /**
   * @CfrPlugin(
   *   id = "related",
   *   label = "Related entity"
   * )
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator(CfrContextInterface $context = NULL) {
    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      [
        EntityToEntity::configurator($context),
        // This one is without context, because we no longer know the entity type.
        EntityDisplay::configurator(),
      ],
      [
        t('Entity relation'),
        t('Related entity display'),
      ]
    );
  }

  /**
   * @param \Drupal\renderkit\EntityToEntity\EntityToEntityInterface $entityToEntity
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface $relatedEntityDisplay
   */
  public function __construct(EntityToEntityInterface $entityToEntity, EntityDisplayInterface $relatedEntityDisplay) {
    $this->entityToEntity = $entityToEntity;
    $this->relatedEntityDisplay = $relatedEntityDisplay;
    $this->relatedEntityType = $entityToEntity->getTargetType();
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
    $relatedEntities = $this->entityToEntity->entitiesGetRelated($entityType, $entities);
    return $this->relatedEntityDisplay->buildEntities($this->relatedEntityType, $relatedEntities);
  }

  /**
   * Same as ->buildEntities(), just for a single entity.
   *
   * @param string $entity_type
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   */
  public function buildEntity($entity_type, $entity) {
    if (NULL === $relatedEntity = $this->entityToEntity->entityGetRelated($entity_type, $entity)) {
      return [];
    }
    return $this->relatedEntityDisplay->buildEntity($this->relatedEntityType, $relatedEntity);
  }
}
