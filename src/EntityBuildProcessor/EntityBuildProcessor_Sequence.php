<?php

namespace Drupal\renderkit\EntityBuildProcessor;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\BuildProcessor\BuildProcessorInterface;

/**
 * An entity build processor that runs each render array through a bunch of
 * processors.
 */
class EntityBuildProcessor_Sequence implements EntityBuildProcessorInterface {

  use EntitiesBuildsProcessorTrait;

  /**
   * @CfrPlugin(
   *   id = "sequence",
   *   label = "Sequence of processors"
   * )
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator(CfrContextInterface $context = NULL) {
    $configurators = [EntityBuildProcessor::sequenceConfigurator($context)];
    $labels = [''];
    return Configurator_CallbackConfigurable::createFromClassStaticMethod(__CLASS__, 'create', $configurators, $labels);
  }

  /**
   * @param \Drupal\renderkit\EntityBuildProcessor\EntityBuildProcessorInterface[] $processors
   *
   * @return self
   */
  public static function create(array $processors) {
    $sequence = new self();
    foreach ($processors as $processor) {
      if ($processor instanceof EntityBuildProcessorInterface) {
        $sequence->addEntityBuildProcessor($processor);
      }
      elseif ($processor instanceof BuildProcessorInterface) {
        $sequence->addBuildProcessor($processor);
      }
    }
    return $sequence;
  }

  /**
   * @var object[]
   */
  private $processors = [];

  /**
   * @param \Drupal\renderkit\BuildProcessor\BuildProcessorInterface $buildProcessor
   *
   * @return $this
   */
  public function addBuildProcessor(BuildProcessorInterface $buildProcessor) {
    $this->processors[] = $buildProcessor;
    return $this;
  }

  /**
   * @param \Drupal\renderkit\EntityBuildProcessor\EntityBuildProcessorInterface $entityBuildProcessor
   *
   * @return $this
   */
  public function addEntityBuildProcessor(EntityBuildProcessorInterface $entityBuildProcessor) {
    $this->processors[] = $entityBuildProcessor;
    return $this;
  }

  /**
   * Processes all the render arrays, by passing them through all the registered
   * processors.
   *
   * @param array[] $builds
   *   The render arrays produced by the decorated display handler.
   * @param string $entity_type
   * @param object[] $entities
   *
   * @return array[]
   *   Modified render arrays for the given entities.
   */
  public function processEntitiesBuilds(array $builds, $entity_type, array $entities) {
    foreach ($this->processors as $processor) {
      if ($processor instanceof EntityBuildProcessorInterface) {
        $builds = $processor->processEntitiesBuilds($builds, $entity_type, $entities);
      }
      elseif ($processor instanceof BuildProcessorInterface) {
        foreach ($builds as $delta => $build) {
          if (!empty($build)) {
            $builds[$delta] = $processor->process($build);
          }
        }
      }
    }
    return $builds;
  }

}
