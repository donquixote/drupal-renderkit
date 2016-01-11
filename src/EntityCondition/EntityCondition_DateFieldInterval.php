<?php

namespace Drupal\renderkit\EntityCondition;

use Drupal\renderkit\EnumMap\EnumMap_FieldName;
use Drupal\cfrapi\Configurator\Id\Configurator_LegendSelect;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;

/**
 * An entity condition that returns true if a given timestamp is contained in
 * one of the date intervals in a given date field on the entity.
 */
class EntityCondition_DateFieldInterval implements EntityConditionInterface {

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var string|int
   */
  private $referenceTimestamp;

  /**
   * @CfrPlugin(
   *   id = "dateFieldInterval",
   *   label = @t("Date field interval")
   * )
   *
   * @param string $entityType
   *   The entity type. Contextual argument.
   * @param string $bundleName
   *   The bundle name. Contextual argument.
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  static function createConfigurator($entityType = NULL, $bundleName = NULL) {
    $legend = new EnumMap_FieldName(array('date'), $entityType, $bundleName);
    $configurators = array(Configurator_LegendSelect::createRequired($legend));
    $labels = array(t('Date field'));
    return Configurator_CallbackConfigurable::createFromClassStaticMethod(__CLASS__, 'createNow', $configurators, $labels);
  }

  /**
   * @param string $fieldName
   *
   * @return \Drupal\renderkit\EntityCondition\EntityCondition_DateFieldInterval
   */
  static function createNow($fieldName) {
    return new self($fieldName, time());
  }

  /**
   * @param string $fieldName
   * @param string|int $referenceTimestamp
   */
  function __construct($fieldName, $referenceTimestamp) {
    $this->fieldName = $fieldName;
    $this->referenceTimestamp = $referenceTimestamp;
  }

  /**
   * @param string $entityType
   * @param object $entity
   *
   * @return bool
   */
  function entityCheckCondition($entityType, $entity) {
    foreach (field_get_items($entityType, $entity, $this->fieldName) ?: array() as $item) {
      if (!isset($item['value']) || $item['value'] <= $this->referenceTimestamp) {
        if (!isset($item['value2']) || $this->referenceTimestamp < $item['value2']) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }
}