<?php

namespace Drupal\renderkit\Configurator\Id;

use Drupal\cfrapi\Configurator\Id\Configurator_SelectBase;

/**
 * Configurator to choose a content entity type.
 */
class Configurator_EntityType_Content extends Configurator_SelectBase {

  /**
   * @return string[]
   */
  protected function getSelectOptions() {

    $options = [];
    foreach (entity_get_info() as $entityType => $entityTypeInfo) {

      if (!empty($entityTypeInfo['configuration'])) {
        continue;
      }

      $options[$entityType] = $entityTypeInfo['label'];
    }

    return $options;
  }

  /**
   * @param string $id
   *
   * @return string|null
   */
  protected function idGetLabel($id) {

    if (NULL === $info = entity_get_info($id)) {
      return NULL;
    }

    if (!empty($info['configuration'])) {
      return NULL;
    }

    return $info['label'];
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  protected function idIsKnown($id) {

    if (NULL === $info = entity_get_info($id)) {
      return FALSE;
    }

    if (!empty($info['configuration'])) {
      return FALSE;
    }

    return TRUE;
  }
}
