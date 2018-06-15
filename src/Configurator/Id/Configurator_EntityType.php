<?php

namespace Drupal\renderkit\Configurator\Id;

use Drupal\cfrapi\Configurator\Id\Configurator_SelectBase;

class Configurator_EntityType extends Configurator_SelectBase {

  /**
   * @return string[][]
   */
  protected function getSelectOptions() {

    $groupLabels = [
      t('Content entity types'),
      t('Configuration entity types'),
    ];

    $optionss = [[], []];
    foreach (entity_get_info() as $entityType => $entityTypeInfo) {
      $groupId = empty($entityTypeInfo['configuration']) ? 0 : 1;
      $optionss[$groupId][$entityType] = $entityTypeInfo['label'];
    }

    $optionss = array_combine($groupLabels, $optionss);
    $optionss = array_filter($optionss);

    return $optionss;
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

    return $info['label'];
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  protected function idIsKnown($id) {
    return NULL !== entity_get_info($id);
  }
}
