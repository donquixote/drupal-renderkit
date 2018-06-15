<?php

namespace Drupal\renderkit\Configurator\Id;

use Drupal\cfrapi\Configurator\Id\Configurator_SelectBase;

/**
 * Configurator for a view mode machine name for a given entity type.
 *
 * This is currently not used anywhere, but may be used by whoever finds it
 * useful.
 */
class Configurator_EntityViewModeName extends Configurator_SelectBase {

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
    $options = [];
    $options['default'] = t('Default');
    if (!empty($entity_info['view modes'])) {
      foreach ($entity_info['view modes'] as $mode => $settings) {
        $options[$mode] = $settings['label'];
      }
    }

    return $options;
  }

  /**
   * @param string $id
   *
   * @return string|null
   */
  protected function idGetLabel($id) {
    if ('default' === $id) {
      return t('Default');
    }
    $entity_info = entity_get_info($this->entityType);
    if (isset($entity_info['view modes'][$id]['label'])) {
      return $entity_info['view modes'][$id]['label'];
    }

    if (isset($entity_info['view modes'][$id])) {
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
    if ('default' === $id) {
      return TRUE;
    }
    $entity_info = entity_get_info($this->entityType);
    return isset($entity_info['view modes'][$id]);
  }
}
