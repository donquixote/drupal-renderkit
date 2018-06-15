<?php

namespace Drupal\renderkit\EntityToEntities;

use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\Configurator\Id\Configurator_FieldName;

class EntityToEntities_EntityReferenceField extends EntityToEntitiesMultipleBase {

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var string
   */
  private $targetType;

  /**
   * @CfrPlugin("entityReferenceField", "Entity reference field")
   *
   * @param string $entityType
   *   (optional) Contextual parameter.
   * @param string $bundleName
   *   (optional) Contextual parameter.
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator($entityType = NULL, $bundleName = NULL) {
    $configurators = [new Configurator_FieldName(['entityreference'], $entityType, $bundleName)];
    $labels = [t('Entity reference field')];
    return Configurator_CallbackConfigurable::createFromClassStaticMethod(__CLASS__, 'create', $configurators, $labels);
  }

  /**
   * @param string $fieldName
   *
   * @return self|null
   */
  public static function create($fieldName) {
    $fieldInfo = field_info_field($fieldName);
    if (NULL === $fieldInfo) {
      throw new \InvalidArgumentException("Field '$fieldName' does not exist.");
    }
    if (!isset($fieldInfo['type'])) {
      throw new \InvalidArgumentException("Field '$fieldName' has no field type.");
    }
    if ($fieldInfo['type'] !== 'entityreference') {
      $typeExport = var_export($fieldInfo['type'], TRUE);
      throw new \InvalidArgumentException("Field type of '$fieldName' expected to be 'entityreference', $typeExport found instead.");
    }
    if (!isset($fieldInfo['settings']['target_type'])) {
      throw new \InvalidArgumentException("No target type in field info.");
    }
    return new self($fieldName, $fieldInfo['settings']['target_type']);
  }

  /**
   * @param string $fieldName
   * @param string $targetType
   */
  public function __construct($fieldName, $targetType) {
    $this->fieldName = $fieldName;
    $this->targetType = $targetType;
  }

  /**
   * @return string
   */
  public function getTargetEntityType() {
    return $this->targetType;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return object[][]
   */
  public function entitiesGetRelated($entityType, array $entities) {

    $truthsByTargetEtid = [];
    $truthsByDeltaAndTargetEtid = [];
    foreach ($entities as $delta => $entity) {
      $entityTruthsByTargetEtid = $this->entityCollectTargetEtidsTruthmap($entityType,$entity);
      $truthsByDeltaAndTargetEtid[$delta] = $entityTruthsByTargetEtid;
      $truthsByTargetEtid += $entityTruthsByTargetEtid;
    }

    $targetEntitiesByEtid = entity_load($this->targetType, array_keys($truthsByTargetEtid));

    $targetEntitiesByDelta = [];
    foreach ($truthsByDeltaAndTargetEtid as $delta => $entityTruthsByTargetEtid) {

      $entityTargetEntities = [];
      foreach ($entityTruthsByTargetEtid as $targetEtid => $truth) {
        if (array_key_exists($targetEtid, $targetEntitiesByEtid)) {
          $entityTargetEntities[] = $targetEntitiesByEtid[$targetEtid];
        }
      }

      $targetEntitiesByDelta[$delta] = $entityTargetEntities;
    }

    return $targetEntitiesByDelta;
  }

  /**
   * @param string $entityType
   * @param object $entity
   *
   * @return true[]
   */
  private function entityCollectTargetEtidsTruthmap($entityType, $entity) {

    $truths_by_target_etid = [];
    foreach (field_get_items($entityType, $entity, $this->fieldName) ?: [] as $itemDelta => $item) {

      if (empty($item['target_id'])) {
        continue;
      }

      $truths_by_target_etid[$item['target_id']] = TRUE;
    }

    return $truths_by_target_etid;
  }
}
