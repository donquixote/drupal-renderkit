<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Configurator\Id\Configurator_FlatOptionsSelect;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\Configurator\Configurator_DsFieldWithSettings;
use Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface;

class EntityDisplay_DsField extends EntityDisplayBase {

  /**
   * @var string
   */
  private $expectedEntityType;

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var array
   */
  private $field;

  /**
   * @var array
   */
  private $labelDisplay;

  /**
   * @var bool
   */
  private $access;

  /**
   * @var \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|null
   */
  private $fieldDisplayProcessor;

  /**
   * @CfrPlugin("dsField", "Display suite field")
   *
   * @param string|null $entity_type
   *   Contextual argument.
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface|null
   */
  public static function plugin($entity_type = NULL) {

    if (!function_exists('ds_get_fields') || !module_exists('ds')) {
      return NULL;
    }

    return Configurator_CallbackConfigurable::createFromClassStaticMethod(
      self::class,
      'fromGroup',
      [
        new Configurator_DsFieldWithSettings($entity_type),
        Configurator_FlatOptionsSelect::createRequired(
        [
          'above' => t('Above'),
          'inline' => t('Inline'),
          'hidden' => '<' . t('Hidden') . '>',
        ],
        'hidden'),
        cfrplugin()->interfaceGetOptionalConfigurator(
          FieldDisplayProcessorInterface::class),
      ],
      [
        t('Display suite field'),
        t('Label display'),
        t('Field display processor'),
      ]
    );
  }

  /**
   * @param array $conf
   * @param string $labelDisplay
   * @param \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|NULL $fieldDisplayProcessor
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplay_DsField|null
   */
  public static function fromGroup(
    array $conf,
    $labelDisplay,
    FieldDisplayProcessorInterface $fieldDisplayProcessor = NULL
  ) {

    if (!function_exists('ds_get_fields') || !module_exists('ds')) {
      return NULL;
    }

    list($entityType, $fieldName) = explode(':', $conf['field']) + [NULL, NULL];
    $settings = isset($conf['display']) ? $conf['display'] : [];
    $settings['label'] = $labelDisplay;

    return self::create(
      $entityType,
      $fieldName,
      $settings,
      $fieldDisplayProcessor);
  }

  /**
   * @param string $expectedEntityType
   *   Expected entity type. If the entity has a different type, the render
   *   array will be empty.
   * @param string $key
   *   Machine name of the DS field.
   * @param array $settings
   *   Settings for the DS field instance.
   * @param \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|null $fieldDisplayProcessor
   *
   * @return self|null
   */
  public static function create(
    $expectedEntityType,
    $key,
    array $settings,
    FieldDisplayProcessorInterface $fieldDisplayProcessor = NULL
  ) {

    if (!function_exists('ds_get_fields') || !module_exists('ds')) {
      return NULL;
    }

    $ds_fields = ds_get_fields($expectedEntityType);

    if (!isset($ds_fields[$key])) {
      return NULL;
    }

    $field = $ds_fields[$key];

    if (isset($settings['format'])) {
      $field['formatter'] = $settings['format'];
    }

    if (isset($settings['formatter_settings'])) {
      $field['formatter_settings'] = $settings['formatter_settings'];
    }

    $labelDisplay = isset($settings['label'])
      ? $settings['label']
      : 'inline';

    $access = 0
      || !function_exists('ds_extras_ds_field_access')
      || !variable_get('ds_extras_field_permissions', FALSE)
      || user_access('view ' . $key . ' on ' . $expectedEntityType);

    if ($key === 'title' && $expectedEntityType === 'node') {

    }

    return new self(
      $expectedEntityType,
      $key,
      $field,
      $labelDisplay,
      $access,
      $fieldDisplayProcessor);
  }

  /**
   * @param string $expectedEntityType
   *   Expected entity type. If the entity has a different type, the render
   *   array will be empty.
   * @param string $fieldName
   *   Machine name of the DS field.
   * @param array $field
   *   Definition array of the DS field.
   * @param string $labelDisplay
   *   Settings for the DS field instance.
   * @param bool $access
   *   TRUE if the current user has access to view the field.
   * @param \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface|null $fieldDisplayProcessor
   */
  public function __construct(
    $expectedEntityType,
    $fieldName,
    array $field,
    $labelDisplay,
    $access,
    FieldDisplayProcessorInterface $fieldDisplayProcessor = NULL
  ) {
    $this->expectedEntityType = $expectedEntityType;
    $this->fieldName = $fieldName;
    $this->field = $field;
    $this->labelDisplay = $labelDisplay;
    $this->access = $access;
    $this->fieldDisplayProcessor = $fieldDisplayProcessor;
  }

  /**
   * Same as ->buildEntities(), just for a single entity.
   *
   * @param string $entity_type
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   *
   * @see \Drupal\renderkit\EntityDisplay\EntityDisplayInterface::buildEntity()
   * @throws \EntityMalformedException
   */
  public function buildEntity($entity_type, $entity) {

    if ($entity_type !== $this->expectedEntityType) {
      return [];
    }

    $key = $this->fieldName;
    $field = $this->field;

    list(,,$bundle) = entity_extract_ids($entity_type, $entity);

    $field_value = ds_get_field_value(
      $this->fieldName,
      $field,
      $entity,
      $entity_type,
      $bundle,
      '_',
      [
        '#bundle' => $bundle,
        '#entity_type' => $entity_type,
      ]);

    if (empty($field_value) && (string) $field_value !== '0') {
      return [];
    }

    // Title label.
    if ($key === 'title' && $entity_type === 'node') {
      $node_type = node_type_get_type($entity);
      $field['title'] = function_exists('i18n_node_translate_type')
        ? i18n_node_translate_type($node_type->type, 'title_label', $node_type->title_label)
        : $node_type->title_label;
    }

    $build = [
      '#theme' => 'field',
      '#field_type' => 'ds',
      '#skip_edit' => TRUE,
      '#title' => $field['title'],
      '#label_display' => $this->labelDisplay,
      '#field_name' => $key,
      '#bundle' => $bundle,
      '#object' => $entity,
      '#entity_type' => $entity_type,
      '#view_mode' => '_',
      '#access' => $this->access,
      '#items' => [['value' => $field_value]],
      0 => ['#markup' => $field_value],
    ];

    if (NULL !== $this->fieldDisplayProcessor) {
      $build = $this->fieldDisplayProcessor->process($build);
    }

    return $build;
  }
}
