<?php

namespace Drupal\renderkit\EntityImage;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\EntityDisplay\EntitiesDisplayBase;

class EntityImage_ChainOfResponsibility extends EntitiesDisplayBase implements EntityImageInterface {

  /**
   * @var \Drupal\renderkit\EntityImage\EntityImageInterface[]
   */
  private $providers;

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
  public static function createConfigurator(CfrContextInterface $context = NULL) {

    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      [
        EntityImage::sequenceConfigurator($context),
      ],
      [
        '',
      ]);
  }

  /**
   * @param \Drupal\renderkit\EntityImage\EntityImageInterface[] $providers
   */
  public function __construct(array $providers) {
    $this->providers = $providers;
  }

  /**
   * Same method signature as in parent interface, just a different description.
   *
   * @param string $entityType
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object[] $entities
   *   Entity objects for which to build the render arrays.
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   *   Each render array must contain '#theme' => 'image'.
   */
  public function buildEntities($entityType, array $entities) {

    $builds = array_fill_keys(array_keys($entities), NULL);

    foreach ($this->providers as $display) {
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
