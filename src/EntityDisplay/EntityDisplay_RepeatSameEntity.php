<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Configurator\Configurator_IntegerInRange;
use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface;

class EntityDisplay_RepeatSameEntity extends EntityDisplayBase {

  /**
   * @var \Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface
   */
  private $entitiesListFormat;

  /**
   * @var int
   */
  private $n;

  /**
   * @CfrPlugin("repeatSameEntity", "Repeat the same entity")
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator(CfrContextInterface $context = NULL) {
    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      [
        cfrplugin()->interfaceGetConfigurator(EntitiesListFormatInterface::class, $context),
        new Configurator_IntegerInRange(1, 100),
      ],
      [
        t('Entities list format'),
        t('Number of repetitions'),
      ]);
  }

  /**
   * @param \Drupal\renderkit\EntitiesListFormat\EntitiesListFormatInterface $entitiesListFormat
   * @param int $n
   */
  public function __construct(EntitiesListFormatInterface $entitiesListFormat, $n) {
    $this->entitiesListFormat = $entitiesListFormat;
    $this->n = $n;
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
    return $this->entitiesListFormat->entitiesBuildList(
      $entity_type,
      array_fill(0, $this->n, $entity));
  }
}
