<?php

namespace Drupal\renderkit\Util;

use Drupal\cfrapi\Exception\ConfToValueException;

final class FieldUtil extends UtilBase {

  /**
   * Loads a field definition, and throws an exception if not successful.
   *
   * @param string $fieldName
   * @param array $allowedTypes
   *
   * @return array
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fieldnameLoadInfoAssertType($fieldName, array $allowedTypes) {

    $fieldInfo = field_info_field($fieldName);

    if (NULL === $fieldInfo) {
      throw new ConfToValueException("Field '$fieldName' does not exist.");
    }

    if (!isset($fieldInfo['type'])) {
      throw new ConfToValueException("Field '$fieldName' has no field type.");
    }

    if (!in_array($fieldInfo['type'], $allowedTypes, TRUE)) {
      $typeExport = var_export($fieldInfo['type'], TRUE);
      $allowedTypesExport = implode(', ', $allowedTypes);
      throw new ConfToValueException("Field type of '$fieldName' expected to be one of $allowedTypesExport, $typeExport found instead.");
    }

    return $fieldInfo;
  }

  /**
   * @param string $fieldName
   * @param string|null $entityType
   * @param string|null $bundleName
   *
   * @return null|string
   */
  public static function fieldnameEtBundleGetLabel($fieldName, $entityType = NULL, $bundleName = NULL) {
    // @todo Maybe this can be done faster?
    $options = self::etBundleGetFieldNameOptions($entityType, $bundleName);
    return isset($options[$fieldName])
      ? $options[$fieldName]
      : NULL;
  }

  /**
   * @param string $fieldName
   * @param string|null $entityType
   * @param string|null $bundleName
   *
   * @return bool
   */
  public static function fieldnameEtBundleExists($fieldName, $entityType = NULL, $bundleName = NULL) {
    // @todo Maybe this can be done faster?
    $options = self::etBundleGetFieldNameOptions($entityType, $bundleName);
    return isset($options[$fieldName]);
  }

  /**
   * @param string|null $entityType
   * @param string|null $bundleName
   *
   * @return string[]
   *   Format: $[$fieldName] = $optionLabel
   */
  public static function etBundleGetFieldNameOptions($entityType = NULL, $bundleName = NULL) {

    $instancesByFieldAndLabel = self::etBundleGetFieldInstancesByFieldAndLabel($entityType, $bundleName);

    $options = [];
    foreach ($instancesByFieldAndLabel as $fieldName => $fieldInstancesByLabel) {
      $options[$fieldName] = implode(' / ', array_keys($fieldInstancesByLabel)) . ' (' . $fieldName . ')';
    }

    return $options;
  }

  /**
   * @param string|null $expectedEntityType
   * @param string|null $expectedBundleName
   *
   * @return array[][]
   *   Format: $[$fieldName][$instanceFieldLabel][] = array($instanceEntityType, $instanceBundleName)
   */
  public static function etBundleGetFieldInstancesByFieldAndLabel($expectedEntityType = NULL, $expectedBundleName = NULL) {

    $instancesByFieldAndLabel = [];
    foreach (self::etBundleGetFieldInstancesByEtBundle($expectedEntityType, $expectedBundleName) as $entityType => $entityTypeInstances) {
      foreach ($entityTypeInstances as $bundleName => $bundleInstances) {
        foreach ($bundleInstances as $fieldName => $instance) {
          $instancesByFieldAndLabel[$fieldName][$instance['label']][] = [$entityType, $bundleName];
        }
      }
    }

    return $instancesByFieldAndLabel;
  }

  /**
   * @param string $entityType
   * @param string $bundleName
   *
   * @return array[][]
   *   Format: $[$entityType][$bundleName] = $fieldInstance
   */
  public static function etBundleGetFieldInstancesByEtBundle($entityType = NULL, $bundleName = NULL) {

    $cache = _field_info_field_cache();
    if (NULL === $entityType) {
      $instances = $cache->getInstances();
    }
    elseif (NULL === $bundleName) {
      $instances[$entityType] = $cache->getInstances($entityType);
    }
    else {
      $instances[$entityType][$bundleName] = $cache->getBundleInstances($entityType, $bundleName);
    }

    return $instances;
  }

  /**
   * @param string $fieldType
   *
   * @return array[]
   */
  public static function fieldTypeGetAvailableFormatterTypes($fieldType) {
    $availableFormatterTypes = [];
    foreach (field_info_formatter_types() as $formatterTypeName => $formatterTypeDefinition) {
      if (!in_array($fieldType, $formatterTypeDefinition['field types'], TRUE)) {
        continue;
      }
      $availableFormatterTypes[$formatterTypeName] = $formatterTypeDefinition;
    }
    return $availableFormatterTypes;
  }

  /**
   * @param string $formatterTypeName
   *
   * @return string|null
   */
  public static function fieldFormatterTypeGetLabel($formatterTypeName) {
    $formatterTypeDefinition = field_info_formatter_types($formatterTypeName);
    if (!isset($formatterTypeDefinition)) {
      return NULL;
    }
    return isset($formatterTypeDefinition['label'])
      ? $formatterTypeDefinition['label']
      : $formatterTypeName;
  }

  /**
   * @param string $formatterTypeName
   *
   * @return bool
   */
  public static function fieldFormatterTypeExists($formatterTypeName) {
    $formatterTypeDefinition = field_info_formatter_types($formatterTypeName);
    return isset($formatterTypeDefinition);
  }

  /**
   * Fake an instance of a field.
   *
   * @param string $fieldName
   *   The unique name for this field no matter what entity/bundle it may be used on.
   * @param string $viewMode
   *   We're building a new view mode for this function.  Defaults to ctools, but we expect developers to actually name this something meaningful.
   * @param string $formatterTypeName
   *   The formatter key selected from the options provided by field_ui_formatter_options().
   * @param array $formatterSettings
   *   An array of key value pairs.  These will be used as #default_value for the form elements generated by a call to hook_field_formatter_settings_form() for this field type.
   *   Typically we'll pass an empty array to begin with and then pass this information back to ourselves on form submit so that we can set the values for later edit sessions.
   *
   * @return array
   *
   * @see ctools_fields_fake_field_instance()
   *   Simply copying this to avoid a dependency.
   */
  public static function createFakeFieldInstance($fieldName, $viewMode, $formatterTypeName, $formatterSettings) {
    $field = field_read_field($fieldName);

    $field_type = field_info_field_types($field['type']);

    return [
      // Build a fake entity type and bundle.
      'field_name' => $fieldName,
      'entity_type' => 'ctools',
      'bundle' => 'ctools',

      // Use the default field settings for settings and widget.
      'settings' => field_info_instance_settings($field['type']),
      'widget' => [
        'type' => $field_type['default_widget'],
        'settings' => [],
      ],

      // Build a dummy display mode.
      'display' => [
        $viewMode => [
          'type' => $formatterTypeName,
          'settings' => $formatterSettings,
        ],
      ],

      // Set the other fields to their default values.
      // @see _field_write_instance().
      'required' => FALSE,
      'label' => $fieldName,
      'description' => '',
      'deleted' => 0,
    ];
  }

  /**
   * @param string[] $allowedFieldTypes
   *   (optional) Allowed field types.
   * @param string $entityType
   *   (optional) An entity type, e.g. "node".
   * @param string $bundleName
   *   (optional) The bundle name, e.g. "article".
   *
   * @return string[]|string[][]|mixed[]
   *   Format: $[$fieldName] = $optionLabel
   */
  public static function fieldTypesGetFieldNameOptions(array $allowedFieldTypes = NULL, $entityType = NULL, $bundleName = NULL) {

    $optionsAll = self::etBundleGetFieldNameOptions($entityType, $bundleName);

    $knownFieldTypes = field_info_field_types();

    $fields = field_info_fields();
    $options = [];
    foreach ($optionsAll as $fieldName => $optionLabel) {
      if (isset($fields[$fieldName]['type'])) {
        $fieldTypeName = $fields[$fieldName]['type'];
        if (isset($allowedFieldTypes) && !in_array($fieldTypeName, $allowedFieldTypes, TRUE)) {
          continue;
        }
        if (isset($knownFieldTypes[$fieldTypeName]['label'])) {
          $fieldTypeLabel = $knownFieldTypes[$fieldTypeName]['label'];
        }
        elseif (isset($knownFieldTypes[$fieldTypeName])) {
          $fieldTypeLabel = $fieldTypeName;
        }
        else {
          $fieldTypeLabel = t('Unknown field type.');
        }
        $options[$fieldTypeLabel][$fieldName] = $optionLabel;
      }
      else {
        if (isset($allowedFieldTypes)) {
          continue;
        }
        $options[$fieldName] = $optionLabel;
      }
    }

    return $options;
  }

}
