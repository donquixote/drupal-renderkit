<?php

namespace Drupal\renderkit\EntityDisplay;

/**
 * Renders the entity in a view mode depending on the entity type.
 *
 * This is currently not registered as a plugin, because it seems an uncommon
 * use case, and would require unnecessarily complex configuration.
 */
class EntityDisplay_ViewModeByType extends EntityDisplay_ViewModeBase {

  /**
   * @var string[]
   */
  private $viewModesByType;

  /**
   * @var string|null
   */
  private $defaultViewModeName;

  /**
   * @param string[] $viewModesByType
   *   Format: $[$entity_type] = $view_mode
   * @param string|null $defaultViewModeName
   */
  public function __construct(array $viewModesByType, $defaultViewModeName = NULL) {
    $this->viewModesByType = $viewModesByType;
    $this->defaultViewModeName = $defaultViewModeName;
  }

  /**
   * @param string $entityType
   *
   * @return string|null
   */
  protected function etGetViewMode($entityType) {

    if (!empty($this->viewModesByType[$entityType])) {
      return $this->viewModesByType[$entityType];
    }

    if (NULL !== $this->defaultViewModeName) {
      return $this->defaultViewModeName;
    }

    return NULL;
  }
}
