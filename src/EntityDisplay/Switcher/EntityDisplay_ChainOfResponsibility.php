<?php

namespace Drupal\renderkit\EntityDisplay\Switcher;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\EntityDisplay\EntitiesDisplayBase;
use Drupal\renderkit\EntityDisplay\EntityDisplay;

/**
 * Chain-of-responsibility entity display switcher.
 */
class EntityDisplay_ChainOfResponsibility extends EntitiesDisplayBase {

  /**
   * The displays to try.
   *
   * @var \Drupal\renderkit\EntityDisplay\EntityDisplayInterface[]
   *   Format: $[] = $displayHandler
   */
  private $displays;

  /**
   * @CfrPlugin(
   *   id = "chain",
   *   label = "Chain of responsibility"
   * )
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createPlugin(CfrContextInterface $context = NULL) {
    $configurators = [EntityDisplay::sequenceConfigurator($context)];
    $labels = [''];
    return Configurator_CallbackConfigurable::createFromClassName(__CLASS__, $configurators, $labels);
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface[] $displays
   */
  public function __construct(array $displays) {
    $this->displays = $displays;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   */
  public function buildEntities($entityType, array $entities) {
    $builds = array_fill_keys(array_keys($entities), NULL);
    foreach ($this->displays as $display) {
      foreach ($display->buildEntities($entityType, $entities) as $delta => $build) {
        if (!empty($build)) {
          if (empty($builds[$delta])) {
            $builds[$delta] = $build;
          }
          unset($entities[$delta]);
        }
      }
      /** @noinspection DisconnectedForeachInstructionInspection */
      if ([] === $entities) {
        break;
      }
    }
    return array_filter($builds);
  }

}
