<?php

namespace Drupal\renderkit\Configurator\Id;

use Drupal\cfrapi\Configurator\Id\Configurator_SelectBase;

/**
 * Configurator to choose a block id.
 *
 * Block ids have the format $module . '.' . $delta.
 */
class Configurator_BlockId extends Configurator_SelectBase {

  /**
   * @return string[][]
   *   Format: $[$module][$module . '.' . $delta] = $label
   */
  protected function getSelectOptions() {

    $optgroups = [];
    foreach (module_implements('block_info') as $module) {
      $optgroup = [];
      $f = $module . '_block_info';
      foreach ($f() ?: [] as $delta => $definition) {
        $optgroup[$module . '.' . $delta] = $definition['info'];
      }
      if ([] === $optgroup) {
        continue;
      }
      $optgroups[$module] = $optgroup;
    }

    ksort($optgroups);

    // @todo Human module labels instead of machine names.
    return $optgroups;
  }

  /**
   * @param string $id
   *
   * @return string|null
   */
  protected function idGetLabel($id) {

    if (NULL === $definition = $this->idGetBlockDefinition($id)) {
      return NULL;
    }

    if (!isset($definition['info'])) {
      return $id;
    }

    return $definition['info'];
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  protected function idIsKnown($id) {

    return NULL !== $this->idGetBlockDefinition($id);
  }

  /**
   * @param string $id
   *
   * @return array|null
   */
  private function idGetBlockDefinition($id) {

    list($module, $delta) = explode('.', $id, 2) + ['', ''];

    if ('' === $delta || '' === $module) {
      return NULL;
    }

    if (!module_exists($module)) {
      return NULL;
    }

    $f = $module . '_block_info';
    if (!\function_exists($f)) {
      return NULL;
    }

    $definitions = $f();
    if (!isset($definitions[$delta])) {
      return NULL;
    }

    return $definitions[$delta];
  }
}
