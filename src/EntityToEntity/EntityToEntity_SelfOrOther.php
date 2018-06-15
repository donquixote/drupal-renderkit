<?php

namespace Drupal\renderkit\EntityToEntity;

class EntityToEntity_SelfOrOther implements EntityToEntityInterface {

  /**
   * @var \Drupal\renderkit\EntityToEntity\EntityToEntityInterface
   */
  private $decorated;

  /**
   * @param \Drupal\renderkit\EntityToEntity\EntityToEntityInterface $decorated
   */
  public function __construct(EntityToEntityInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Gets the entity type of the referenced entities.
   *
   * @return string
   */
  public function getTargetType() {
    return $this->decorated->getTargetType();
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return object[]
   */
  public function entitiesGetRelated($entityType, array $entities) {
    if ($entityType === $this->decorated->getTargetType()) {
      return $entities;
    }

    return $this->decorated->entitiesGetRelated($entityType, $entities);
  }

  /**
   * @param string $entityType
   * @param object $entity
   *
   * @return object|null
   */
  public function entityGetRelated($entityType, $entity) {
    if ($entityType === $this->getTargetType()) {
      return $entity;
    }

    return $this->decorated->entityGetRelated($entityType, $entity);
  }
}
