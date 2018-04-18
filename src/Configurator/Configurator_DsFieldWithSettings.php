<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrfamily\Configurator\Composite\Configurator_IdConfBase;

class Configurator_DsFieldWithSettings extends Configurator_IdConfBase {

  /**
   * @var string|null
   */
  private $entityType;

  public static function create() {

  }

  /**
   * @param string|null $entityType
   */
  public function __construct($entityType = NULL) {
    parent::__construct(TRUE, $this, 'field', 'display');
    $this->entityType = $entityType;
  }

  /**
   * @param string $etWithFieldName
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  protected function idGetConfigurator($etWithFieldName) {

    list($entityType, $fieldName) = explode(':', $etWithFieldName) + [NULL, NULL];

    if (NULL !== $this->entityType && $entityType !== $this->entityType) {
      return NULL;
    }

    $ds_fields = ds_get_fields($entityType);

    if (!isset($ds_fields[$fieldName])) {
      return NULL;
    }

    return new Configurator_DsFieldFormat($ds_fields[$fieldName]);
  }

  /**
   * @param string $etWithFieldName
   *
   * @return string|null
   */
  protected function idGetOptionsFormLabel($etWithFieldName) {
    return t('Formatter');
  }

  /**
   * @return string[][]|string[]
   */
  protected function getSelectOptions() {

    if (NULL !== $this->entityType) {
      return $this->etGetOptions($this->entityType);
    }

    $options = [];
    foreach (entity_get_info() as $entityType => $entityTypeInfo) {
      if (!empty($entityTypeInfo['configuration'])) {
        continue;
      }
      $entityTypeOptions = $this->etGetOptions($entityType);
      if ([] === $entityTypeOptions) {
        continue;
      }
      $entityTypeLabel = $entityTypeInfo['label'];
      foreach ($entityTypeOptions as $fieldName => $fieldLabel) {
        $options[$entityTypeLabel][$fieldName] = $fieldLabel . ' (' . $entityTypeLabel. ')';
      }
    }

    return $options;
  }

  /**
   * @param string $entityType
   *
   * @return string[]
   */
  private function etGetOptions($entityType) {

    $ds_fields = ds_get_fields($entityType);

    $options = [];
    foreach ($ds_fields as $key => $field) {
      $options[$entityType . ':' . $key] = $field['title'];
    }

    return $options;
  }

  /**
   * @param string $etWithFieldName
   *
   * @return string
   */
  protected function idGetLabel($etWithFieldName) {

    list($entityType, $fieldName) = explode(':', $etWithFieldName) + [NULL, NULL];

    if (NULL !== $this->entityType && $entityType !== $this->entityType) {
      return NULL;
    }

    $ds_fields = ds_get_fields($entityType);

    if (!isset($ds_fields[$fieldName])) {
      return NULL;
    }

    return $ds_fields[$fieldName]['title'];
  }

  public function confGetValue($conf) {
    $value = parent::confGetValue($conf);
    return $value;
  }
}
