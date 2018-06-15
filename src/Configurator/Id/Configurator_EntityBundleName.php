<?php

namespace Drupal\renderkit\Configurator\Id;

use Drupal\cfrapi\Configurator\Id\Configurator_SelectBase;

class Configurator_EntityBundleName extends Configurator_SelectBase {

  /**
   * @var string
   */
  private $entityType;

  /**
   * @param string $entityType
   * @param bool $required
   */
  public function __construct($entityType, $required = TRUE) {
    $this->entityType = $entityType;
    parent::__construct($required);
  }

  /**
   * @return mixed[]
   */
  protected function getSelectOptions() {
    $entity_info = entity_get_info($this->entityType);
    if (empty($entity_info['bundles'])) {
      return [];
    }
    $options = [];
    foreach ($entity_info['bundles'] as $bundle => $bundle_info) {
      $options[$bundle] = $bundle_info['label'];
    }
    return $options;
  }

  /**
   * @param string $id
   *
   * @return string|null
   */
  protected function idGetLabel($id) {
    $entity_info = entity_get_info($this->entityType);
    if (isset($entity_info['bundles'][$id]['label'])) {
      return $entity_info['bundles'][$id]['label'];
    }

    if (isset($entity_info['bundles'][$id])) {
      return $id;
    }

    return NULL;
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  protected function idIsKnown($id) {
    $entity_info = entity_get_info($this->entityType);
    return isset($entity_info['bundles'][$id]);
  }
}
