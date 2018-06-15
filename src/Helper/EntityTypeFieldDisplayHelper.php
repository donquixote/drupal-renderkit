<?php

namespace Drupal\renderkit\Helper;

class EntityTypeFieldDisplayHelper {

  /**
   * @var string
   */
  private $entityType;

  /**
   * @var string
   */
  private $idKey;

  /**
   * @var string
   */
  private $bundleKey;

  /**
   * @var bool
   */
  private $hasTranslationHandler;

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var array
   */
  private $fieldInfo;

  /**
   * @var array
   */
  private $display;

  /**
   * @var string
   */
  private $formatterFunction;

  /**
   * @var string
   */
  private $langcode;

  /**
   * @param string $entityType
   * @param string $fieldName
   * @param array $display
   * @param string $langcode
   *
   * @return static|null
   */
  public static function create($entityType, $fieldName, array $display, $langcode) {
    $entityTypeInfo = entity_get_info($entityType);
    if (empty($entityTypeInfo['entity keys']['id'])) {
      return NULL;
    }
    $idKey = $entityTypeInfo['entity keys']['id'];
    $bundleKey = !empty($entityTypeInfo['entity keys']['bundle'])
      ? $entityTypeInfo['entity keys']['bundle']
      : NULL;

    $fieldInfo = field_info_field($fieldName);
    if (empty($fieldInfo)) {
      return NULL;
    }
    $cache = _field_info_field_cache();
    $display = $cache->prepareInstanceDisplay($display, $fieldInfo["type"]);
    if ($display['type'] === 'hidden') {
      return NULL;
    }
    $formatterFunction = $display['module'] . '_field_formatter_view';
    if (!\function_exists($formatterFunction)) {
      return NULL;
    }
    $langcode = field_valid_language($langcode, FALSE);
    $hasTranslationHandler = field_has_translation_handler($entityType);
    return new static($entityType, $idKey, $bundleKey, $hasTranslationHandler, $fieldName, $fieldInfo, $display, $formatterFunction, $langcode);
  }

  /**
   * @param string $entityType
   * @param string $idKey
   * @param string $bundleKey
   * @param bool $hasTranslationHandler
   * @param string $fieldName
   * @param array $fieldInfo
   * @param array $display
   * @param string $formatterFunction
   * @param string $langcode
   */
  protected function __construct($entityType, $idKey, $bundleKey, $hasTranslationHandler, $fieldName, array $fieldInfo, array $display, $formatterFunction, $langcode) {
    $this->entityType = $entityType;
    $this->idKey = $idKey;
    $this->bundleKey = $bundleKey;
    $this->hasTranslationHandler = $hasTranslationHandler;
    $this->fieldName = $fieldName;
    $this->fieldInfo = $fieldInfo;
    $this->display = $display;
    $this->formatterFunction = $formatterFunction;
    $this->langcode = $langcode;
  }

  /**
   * Builds field render arrays for a series of entities.
   *
   * @param object[] $entitiesByDelta
   *   Format: $[$delta] = $entity
   *   Entities in custom order by custom keys.
   *
   * @return array[]
   *   Render arrays for each entity, with the original custom order and keys.
   */
  public function buildMultipleByDelta(array $entitiesByDelta) {

    $entitiesByBundleAndId = [];
    $idsByDelta = [];
    foreach ($entitiesByDelta as $delta => $entity) {
      if (empty($entity->{$this->fieldName})) {
        continue;
      }
      if (!isset($entity->{$this->idKey})) {
        // Objects being created might not have id/vid yet.
        // @todo Create a temporary UUID ?
        continue;
      }
      $id = $entity->{$this->idKey};
      $idsByDelta[$delta] = $id;
      if (isset($this->bundleKey)) {
        // Explicitly fail for malformed entities missing the bundle property.
        if (!isset($entity->{$this->bundleKey}) || $entity->{$this->bundleKey} === '') {
          // @todo Optionally let the developer know that something is wrong.
          continue;
        }
        $entitiesByBundleAndId[$entity->{$this->bundleKey}][$id] = $entity;
      }
      else {
        $entitiesByBundleAndId[$this->entityType][$id] = $entity;
      }
    }

    $buildsById = $this->buildMultipleByBundleAndId($entitiesByBundleAndId);

    $builds = [];
    foreach ($idsByDelta as $delta => $etid) {
      if (isset($buildsById[$etid])) {
        $builds[$delta] = $buildsById[$etid];
      }
    }
    return $builds;
  }

  /**
   * @param object[][] $entitiesByBundleAndId
   *   Format: $[$bundle_name][$entity_id] = $entity
   *
   * @return array[]
   *   Format: $[$entity_id] = $build
   */
  private function buildMultipleByBundleAndId(array $entitiesByBundleAndId) {

    $entitiesByLanguageAndId = [];
    $instancesByLanguageAndId = [];
    $bundlesById = [];
    foreach ($entitiesByBundleAndId as $bundle => $bundleEntitiesById) {
      $instance = field_info_instance($this->entityType, $this->fieldName, $bundle);
      if (empty($instance)) {
        // Skip these entities.
        continue;
      }
      foreach ($bundleEntitiesById as $id => $entity) {
        $entityFieldDisplayLanguage = $this->entityFieldLanguage($entity);
        if ($entityFieldDisplayLanguage === FALSE) {
          continue;
        }
        $bundlesById[$id] = $bundle;
        /** @var string $entityFieldDisplayLanguage */
        $instancesByLanguageAndId[$entityFieldDisplayLanguage][$id] = $instance;
        $entitiesByLanguageAndId[$entityFieldDisplayLanguage][$id] = $entity;
      }
    }

    // Invoke the field hook and collect results.

    $buildsById = [];
    foreach ($entitiesByLanguageAndId as $entityFieldDisplayLanguage => $languageEntitiesById) {
      $buildsById += $this->languageEntitiesBuildField(
        $languageEntitiesById,
        $bundlesById,
        $instancesByLanguageAndId[$entityFieldDisplayLanguage],
        $entityFieldDisplayLanguage);
    }

    return $buildsById;
  }

  /**
   * @param object[] $languageEntitiesById
   *   Format: $[$entity_id] = $entity
   * @param string[] $bundlesById
   *   Format: $[$entity_id] = $bundle_name
   * @param array[] $instancesById
   *   Format: $[$entity_id] = $field_instance_info
   * @param string $entityFieldDisplayLanguage
   *   E.g. 'en'.
   *
   * @return array[]
   *   Format: $[$entity_id] = $build
   */
  private function languageEntitiesBuildField(array $languageEntitiesById, array $bundlesById, array $instancesById, $entityFieldDisplayLanguage) {

    $itemsByEntityId = $this->languageEntitiesCollectFieldItems($languageEntitiesById, $instancesById, $entityFieldDisplayLanguage);

    $buildsById = [];
    foreach ($languageEntitiesById as $id => $entity) {
      $buildsById[$id] = $this->entityBuildField($entity, $bundlesById[$id], $instancesById[$id], $entityFieldDisplayLanguage, $itemsByEntityId[$id]);
    }

    return $buildsById;
  }

  /**
   * @param object[] $languageEntitiesById
   * @param array[] $instancesById
   * @param string $entityFieldDisplayLanguage
   *
   * @return array[][]
   */
  private function languageEntitiesCollectFieldItems(array $languageEntitiesById, array $instancesById, $entityFieldDisplayLanguage) {
    $itemsByEntityId = $this->languageEntitiesCollectRawFieldItems($languageEntitiesById, $entityFieldDisplayLanguage);
    return $this->languageEntitiesPrepareFieldItems($itemsByEntityId, $languageEntitiesById, $instancesById, $entityFieldDisplayLanguage);
  }

  /**
   * @param object[] $languageEntitiesById
   * @param string $entityFieldDisplayLanguage
   *
   * @return array[][]
   */
  private function languageEntitiesCollectRawFieldItems(array $languageEntitiesById, $entityFieldDisplayLanguage) {

    $itemsByEntityId = [];
    foreach ($languageEntitiesById as $id => $entity) {
      $itemsByEntityId[$id] = isset($entity->{$this->fieldName}[$entityFieldDisplayLanguage])
        ? $entity->{$this->fieldName}[$entityFieldDisplayLanguage]
        : [];
    }

    return $itemsByEntityId;
  }

  /**
   * @param array[][] $itemsByEntityId
   * @param object[] $languageEntitiesById
   * @param array[] $instancesById
   * @param string $entityFieldDisplayLanguage
   *
   * @return array[][]
   */
  private function languageEntitiesPrepareFieldItems(array $itemsByEntityId, array $languageEntitiesById, array $instancesById, $entityFieldDisplayLanguage) {

    /* @see hook_field_prepare_view() */
    $function = $this->fieldInfo['module'] . '_field_prepare_view';
    if (\function_exists($function)) {
      $null = NULL;
      $function($this->entityType, $languageEntitiesById, $this->fieldInfo, $instancesById, $entityFieldDisplayLanguage, $itemsByEntityId, $this->display, $null);
    }

    /* @see hook_field_formatter_prepare_view() */
    $function = $this->display['module'] . '_field_formatter_prepare_view';
    if (\function_exists($function)) {
      $displaysByEntityId = array_fill_keys(array_keys($languageEntitiesById), $this->display);
      $function($this->entityType, $languageEntitiesById, $this->fieldInfo, $instancesById, $entityFieldDisplayLanguage, $itemsByEntityId, $displaysByEntityId);
    }

    return $itemsByEntityId;
  }

  /**
   * @param object $entity
   * @param string $bundle
   * @param array $instance
   * @param string $entityFieldDisplayLanguage
   * @param array[] $items
   *
   * @return array|null
   */
  private function entityBuildField($entity, $bundle, $instance, $entityFieldDisplayLanguage, array $items) {
    $formatterFunction = $this->formatterFunction;
    $build = $formatterFunction($this->entityType, $entity, $this->fieldInfo, $instance, $entityFieldDisplayLanguage, $items, $this->display);
    if (empty($build)) {
      return NULL;
    }

    $build_defaults = [
      '#theme' => 'field',
      # '#weight' => $this->display['weight'],
      '#title' => $instance['label'],
      /* @see hook_field_access() */
      '#access' => field_access('view', $this->fieldInfo, $this->entityType, $entity),
      '#label_display' => $this->display['label'],
      '#view_mode' => '_custom_display',
      '#language' => $entityFieldDisplayLanguage,
      '#field_name' => $this->fieldName,
      '#field_type' => $this->fieldInfo['type'],
      '#field_translatable' => $this->fieldInfo['translatable'],
      '#entity_type' => $this->entityType,
      '#bundle' => $bundle,
      '#object' => $entity,
      '#items' => $items,
      '#formatter' => $this->display['type'],
    ];
    $build += $build_defaults;

    // Invoke hook_field_attach_view_alter() to let other modules alter the
    // renderable array, as in a full field_attach_view() execution.
    $context = [
      'entity_type' => $this->entityType,
      'entity' => $entity,
      'view_mode' => '_custom',
      'display' => $this->display,
      'language' => $entityFieldDisplayLanguage,
    ];
    $result = [$this->fieldName => $build];
    /* @see hook_field_attach_view_alter() */
    drupal_alter('field_attach_view', $result, $context);
    if (!isset($result[$this->fieldName])) {
      // The alter hook has denied this field.
      return NULL;
    }

    return $result[$this->fieldName];
  }

  /**
   * @param object $entity
   *
   * @return string|bool
   *
   * @see field_language()
   */
  private function entityFieldLanguage($entity) {
    $langcode = isset($entity->{$this->fieldName}[$this->langcode])
      ? $this->langcode
      : LANGUAGE_NONE;

    if ($this->hasTranslationHandler) {
      $context = [
        'entity_type' => $this->entityType,
        'entity' => clone $entity,
        'language' => $langcode,
      ];
      $languageByField = [$this->fieldName => $langcode];
      /* @see hook_field_language_alter() */
      drupal_alter('field_language', $languageByField, $context);
      $langcode = isset($languageByField[$this->fieldName])
        ? $languageByField[$this->fieldName]
        : FALSE;
    }

    return $langcode;
  }

}
