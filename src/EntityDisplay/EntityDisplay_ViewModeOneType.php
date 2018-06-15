<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Exception\ConfToValueException;
use Drupal\cfrreflection\Configurator\Configurator_CallbackMono;
use Drupal\renderkit\Configurator\Id\Configurator_EntityTypeWithViewModeName;

/**
 * Renders the entity with a view mode, but only if it has the expected type.
 */
class EntityDisplay_ViewModeOneType extends EntityDisplay_ViewModeBase {

  /**
   * @var string
   */
  private $entityType;

  /**
   * @var string
   */
  private $viewMode;

  /**
   * @CfrPlugin(
   *   id = "viewMode",
   *   label = @t("View mode")
   * )
   *
   * @param string $entityType
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator($entityType = NULL) {

    # $configurators = [];
    # $labels = [t('View mode')];

    return Configurator_CallbackMono::createFromClassStaticMethod(
      __CLASS__,
      /* @see createFromId() */
      'createFromId',
      new Configurator_EntityTypeWithViewModeName($entityType));
  }

  /**
   * @param string $id
   *   A combination of entity type and view mode name.
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplay_ViewModeOneType
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function createFromId($id) {

    if (!is_string($id)) {
      throw new ConfToValueException("Id must be a string.");
    }

    if ('' === $id) {
      throw new ConfToValueException("Id must not be empty.");
    }

    list($type, $mode) = explode(':', $id . ':');

    if ('' === $type) {
      throw new ConfToValueException("Entity type must not be empty.");
    }

    if ('' === $mode) {
      throw new ConfToValueException("View mode name must not be empty.");
    }

    return new self($type, $mode);
  }

  /**
   * @param string $entityType
   * @param string $viewMode
   */
  public function __construct($entityType, $viewMode) {
    $this->entityType = $entityType;
    $this->viewMode = $viewMode;
  }

  /**
   * @param string $entityType
   *
   * @return string|null
   */
  protected function etGetViewMode($entityType) {

    if ($entityType !== $this->entityType) {
      return NULL;
    }

    return $this->viewMode;
  }
}
