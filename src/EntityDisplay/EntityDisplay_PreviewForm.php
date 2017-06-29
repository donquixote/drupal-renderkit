<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Context\CfrContext;
use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrapi\Exception\InvalidConfigurationException;

class EntityDisplay_PreviewForm extends EntityDisplayBase {

  /**
   * @var true[]
   *   Format: $[$urlKey] = TRUE
   */
  private static $inProgress = [];

  /**
   * @var string
   */
  private $queryKey;

  /**
   * @CfrPlugin("previewForm", "Preview form")
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplay_PreviewForm
   */
  public static function create() {
    return new self('entity-display-preview');
  }

  /**
   * @param string $queryKey
   */
  public function __construct($queryKey) {
    $this->queryKey = $queryKey;
  }

  /**
   * @param string $entity_type
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   *
   * @see \Drupal\renderkit\EntityDisplay\EntityDisplayInterface::buildEntity()
   */
  public function buildEntity($entity_type, $entity) {

    if (!empty(self::$inProgress[$this->queryKey])) {
      return ['#markup' => t('Recursion detected.')];
    }
    self::$inProgress[$this->queryKey] = TRUE;

    try {
      return $this->doBuildEntity($entity_type, $entity);
    }
    finally {
      unset(self::$inProgress[$this->queryKey]);
    }
  }


  /**
   * @param string $entity_type
   *   E.g. 'node' or 'taxonomy_term'.
   * @param object $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   *
   * @see drupal_get_form()
   * @see drupal_build_form()
   * @see drupal_prepare_form()
   * @see drupal_process_form()
   * @see form_builder()
   */
  private function doBuildEntity($entity_type, $entity) {

    $conf = isset($_GET[$this->queryKey])
      ? $_GET[$this->queryKey]
      : NULL;

    $context = $this->etBundleBuildContext($entity_type, $entity);

    $build = [];
    $build['form'] = $this->buildForm($conf, $context);

    if (empty($conf)) {
      return $build;
    }

    try {
      $entityDisplay = cfrplugin()
        ->interfaceGetOptionalConfigurator(
          EntityDisplayInterface::class, $context)
        ->confGetValue($conf);
    }
    catch (InvalidConfigurationException $e) {
      drupal_set_message(t('Failed to construct the EntityDisplay object from the configuration provided.'));
      return $build;
    }

    if (!$entityDisplay instanceof EntityDisplayInterface) {
      if (is_object($entityDisplay)) {
        $build['preview']['#markup'] = t(
          'The configurator returned an object that does not implement @interface.',
          ['@interface' => 'EntityDisplayInterface']);
      }
      else {
        $build['preview']['#markup'] = t(
          'The configurator returned a non-object.');
      }

      return $build;
    }

    $build['preview'] = $entityDisplay->buildEntity(
      $entity_type,
      $entity);

    return $build;
  }

  /**
   * @param string $entityType
   * @param object $bundle
   *
   * @return \Drupal\cfrapi\Context\CfrContextInterface
   */
  private function etBundleBuildContext($entityType, $entity) {
    list(,,$bundle) = entity_extract_ids($entityType, $entity);
    return CfrContext::create()
      ->paramNameSetValue('entityType', $entityType)
      ->paramNameSetValue('entity_type', $entityType)
      ->paramNameSetValue('bundle', $bundle)
      ->paramNameSetValue('bundle_name', $bundle)
      ->paramNameSetValue('bundleName', $bundle);
  }

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return array
   */
  private function buildForm($conf, CfrContextInterface $context) {

    $form = [];
    $form['entity_display'] = [
      '#title' => t('Entity display plugin'),
      /* @see cfrplugin_element_info() */
      '#type' => 'cfrplugin',
      '#cfrplugin_interface' => EntityDisplayInterface::class,
      '#cfrplugin_context' => $context,
      '#default_value' => $conf,
      '#required' => TRUE,
    ];

    $form['query_key'] = [
      '#type' => 'value',
      '#value' => $this->queryKey,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Show'),
    ];

    $form['#submit'][] = [self::class, 'submit'];

    /* @see _renderkit_generic_form() */
    return drupal_get_form('_renderkit_generic_form', $form);
  }

  /**
   * Form submit callback.
   *
   * @param array $form
   * @param array $form_state
   */
  public static function submit(
    /** @noinspection PhpUnusedParameterInspection */ array $form,
    array &$form_state
  ) {
    $queryKey = $form_state['values']['query_key'];
    $conf = $form_state['values']['entity_display'];

    drupal_goto(
      $_GET['q'],
      [
        'query' => [$queryKey => $conf],
      ]);
  }
}
