<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\renderkit\BuildProvider\BuildProviderInterface;

/**
 * @CfrPlugin("buildProvider", @t("Build provider"))
 */
class EntityDisplay_BuildProvider extends EntityDisplayBase {

  /**
   * @var \Drupal\renderkit\BuildProvider\BuildProviderInterface
   */
  private $buildProvider;

  /**
   * @param \Drupal\renderkit\BuildProvider\BuildProviderInterface $buildProvider
   */
  public function __construct(BuildProviderInterface $buildProvider) {
    $this->buildProvider = $buildProvider;
  }

  /**
   * @param string $entity_type
   * @param object $entity
   *
   * @return array
   */
  public function buildEntity($entity_type, $entity) {
    return $this->buildProvider->build();
  }

}
