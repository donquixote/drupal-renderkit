<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\ConfiguratorInterface;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;
use Drupal\renderkit\Util\FieldUtil;

class Configurator_FieldFormatterSettings implements ConfiguratorInterface {

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var array
   */
  private $fieldInfo;

  /**
   * @var string
   */
  private $formatterType;

  /**
   * @var array
   */
  private $formatterTypeInfo;

  /**
   * @param string $fieldName
   * @param string $formatterType
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function create($fieldName, $formatterType) {
    /* @see hook_field_formatter_info() */
    $formatterTypeInfo = field_info_formatter_types($formatterType);
    if (empty($formatterTypeInfo)) {
      return NULL;
    }
    if (!isset($formatterTypeInfo['field types'])) {
      return NULL;
    }
    $fieldInfo = field_info_field($fieldName);
    if (!isset($fieldInfo)) {
      return NULL;
    }
    if (!isset($fieldInfo['type'])) {
      return NULL;
    }
    $fieldType = $fieldInfo['type'];
    if (!\in_array($fieldType, $formatterTypeInfo['field types'], TRUE)) {
      return NULL;
    }
    return new self($fieldName, $fieldInfo, $formatterType, $formatterTypeInfo);
  }

  /**
   * @param string $fieldName
   * @param array $fieldInfo
   * @param string $formatterType
   * @param array $formatterTypeInfo
   */
  public function __construct($fieldName, array $fieldInfo, $formatterType, array $formatterTypeInfo) {
    $this->fieldName = $fieldName;
    $this->fieldInfo = $fieldInfo;
    $this->formatterType = $formatterType;
    $this->formatterTypeInfo = $formatterTypeInfo;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param string|null $label
   *   Label for the form element, specifying the purpose where it is used.
   *
   * @return array
   */
  public function confGetForm($conf, $label) {
    /* @see hook_field_formatter_settings_form() */
    $function = $this->formatterTypeInfo['module'] . '_field_formatter_settings_form';
    if (!\function_exists($function)) {
      return [];
    }
    $settings = $this->confGetFormatterSettings($conf);
    $instance = FieldUtil::createFakeFieldInstance($this->fieldName, '_custom', $this->formatterType, $settings);
    $form = [];
    $form_state = [];
    $settings_form = $function($this->fieldInfo, $instance, '_custom', $form, $form_state);

    if (!\count($settings_form)) {
      return [];
    }
    return $settings_form;
    # return array('settings' => $settings_form);
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *
   * @return null|string
   */
  public function confGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {
    $settings = $this->confGetFormatterSettings($conf);
    $instance = FieldUtil::createFakeFieldInstance($this->fieldName, '_custom', $this->formatterType, $settings);
    /* @see hook_field_formatter_settings_summary() */
    return module_invoke($this->formatterTypeInfo['module'], 'field_formatter_settings_summary', $this->fieldInfo, $instance, '_custom');
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   *
   * @return mixed
   *   Value to be used in the application.
   */
  public function confGetValue($conf) {
    return $this->confGetFormatterSettings($conf);
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement to generate the value.
   */
  public function confGetPhp($conf, CfrCodegenHelperInterface $helper) {
    return var_export($this->confGetFormatterSettings($conf), TRUE);
  }

  /**
   * @param mixed $conf
   *
   * @return array
   */
  private function confGetFormatterSettings($conf) {
    $settings = \is_array($conf) ? $conf : [];
    # $settings = isset($conf['settings']) ? $conf['settings'] : array();
    return $settings + $this->formatterTypeInfo['settings'];
  }
}
