<?php

namespace Drupal\renderkit\EntitiesListFormat;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackMono;
use Drupal\renderkit\EntityDisplay\EntityDisplay;

class EntitiesListFormat_SimpleTable implements EntitiesListFormatInterface {

  /**
   * @var \Drupal\renderkit\EntityDisplay\EntityDisplayInterface[]
   */
  private $columnDisplays;

  /**
   * @CfrPlugin("simpleTable", "Simple table")
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator(CfrContextInterface $context = NULL) {
    return Configurator_CallbackMono::createFromClassName(
      self::class,
      EntityDisplay::sequenceConfigurator($context));
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface[] $columnDisplays
   */
  public function __construct(array $columnDisplays) {
    $this->columnDisplays = $columnDisplays;
  }

  /**
   * Displays the entities as a list, e.g. as a table.
   *
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array
   *   A render array.
   */
  public function entitiesBuildList($entityType, array $entities) {

    $rows = [];
    foreach ($this->columnDisplays as $colKey => $columnDisplay) {
      foreach ($columnDisplay->buildEntities($entityType, $entities) as $rowKey => $build) {
        $rows[$rowKey][$colKey] = drupal_render($build);
      }
    }

    return [
      /* @see theme_table() */
      '#theme' => 'table',
      '#rows' => $rows,
    ];
  }
}
