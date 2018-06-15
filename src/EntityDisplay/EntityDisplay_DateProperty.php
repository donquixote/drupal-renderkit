<?php

namespace Drupal\renderkit\EntityDisplay;

class EntityDisplay_DateProperty extends EntityDisplayBase {

  /**
   * @var string
   */
  private $key;

  /**
   * @var string
   */
  private $dateFormatType = 'medium';

  /**
   * @var string
   */
  private $dateFormatCustom = '';

  /**
   * @param string $key
   *   E.g. 'created'.
   */
  public function __construct($key) {
    $this->key = $key;
  }

  /**
   * @param string $type
   *   The format to use, one of:
   *   - 'short', 'medium', or 'long' (the corresponding built-in date formats).
   *   - The name of a date type defined by a module in hook_date_format_types(),
   *     if it's been assigned a format.
   *   - The machine name of an administrator-defined date format.
   */
  public function setDateFormatType($type) {
    $this->dateFormatType = $type;
    $this->dateFormatCustom = '';
  }

  /**
   * @param string $format
   */
  public function setDateFormatCustom($format) {
    $this->dateFormatType = 'custom';
    $this->dateFormatCustom = $format;
  }

  /**
   * @param string $entity_type
   * @param object $entity
   *
   * @return array
   *   Render array for one entity.
   */
  public function buildEntity($entity_type, $entity) {
    if ('node' !== $entity_type) {
      return [];
    }
    if (!isset($entity->{$this->key})) {
      return [];
    }
    $date = $entity->{$this->key};
    if (!\is_int($date)) {
      // Not a timestamp.
      return [];
    }
    return [
      '#markup' => format_date($date, $this->dateFormatType, $this->dateFormatCustom)
    ];
  }
}
