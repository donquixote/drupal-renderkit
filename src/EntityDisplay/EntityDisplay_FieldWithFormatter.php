<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\renderkit\Configurator\Configurator_EntityDisplay_FieldWithFormatter;
use Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface;
use Drupal\renderkit\Helper\EntityTypeFieldDisplayHelper;

/**
 * Entity display handler to view a specific field on all the entities.
 */
class EntityDisplay_FieldWithFormatter extends EntitiesDisplayBase {

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var array
   */
  private $display;

  /**
   * @var \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|null
   */
  private $fieldDisplayProcessor;

  /**
   * @var string|null
   */
  private $langcode;

  /**
   * @CfrPlugin("fieldWithFormatter", @t("Field with formatter"))
   *
   * @param string|null $entityType
   * @param string|null $bundleName
   *
   * @return \Drupal\renderkit\Configurator\Configurator_EntityDisplay_FieldWithFormatter
   */
  public static function configurator($entityType = NULL, $bundleName = NULL) {
    return new Configurator_EntityDisplay_FieldWithFormatter($entityType, $bundleName);
  }

  /**
   * @param string $field_name
   * @param array $display
   * @param \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|null $fieldDisplayProcessor
   * @param string $langcode
   */
  public function __construct($field_name, array $display = [], FieldDisplayProcessorInterface $fieldDisplayProcessor = NULL, $langcode = NULL) {
    $this->fieldName = $field_name;
    $this->display = $display + ['label' => 'hidden'];
    $this->fieldDisplayProcessor = $fieldDisplayProcessor;
    $this->langcode = $langcode;
  }

  /**
   * Sets the field formatter.
   *
   * @param string $name
   *   The machine name of the field formatter.
   * @param array $settings
   *   The formatter settings.
   *
   * @return $this
   */
  public function setFormatter($name, array $settings = NULL) {
    $this->display['type'] = $name;
    if (isset($settings)) {
      $this->display['settings'] = $settings;
    }
    return $this;
  }

  /**
   * @param string $label_position
   *   The default implementation supports 'above', 'inline' and 'hidden'.
   *   Default in core is 'above', but default here is 'hidden'.
   */
  public function setLabelPosition($label_position) {
    $this->display['label'] = $label_position;
  }

  /**
   * @param string $entityType
   * @param array $entities
   *
   * @return array
   */
  public function buildEntities($entityType, array $entities) {
    $helper = EntityTypeFieldDisplayHelper::create($entityType, $this->fieldName, $this->display, $this->langcode);
    $builds = $helper->buildMultipleByDelta($entities);
    if (NULL !== $this->fieldDisplayProcessor) {
      foreach ($builds as $delta => $build) {
        $builds[$delta] = $this->fieldDisplayProcessor->process($build);
      }
    }
    return $builds;
  }

}
