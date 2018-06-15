<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\ConfiguratorInterface;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;

class Configurator_DsFieldFormat implements ConfiguratorInterface {

  /**
   * @var array
   */
  private $field;

  /**
   * @param array $info
   */
  public function __construct(array $info) {
    $this->field = $info;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param string|null $label
   *   Label for the form element, specifying the purpose where it is used.
   *
   * @return array
   *
   * @see ds_field_settings_form()
   */
  public function confGetForm($conf, $label) {
    if (!\is_array($conf)) {
      $conf = [];
    }

    $form = [];

    if (isset($this->field['properties']['formatters'])) {
      $form['format'] = [
        '#type' => 'select',
        '#options' => $this->field['properties']['formatters'],
        '#default_value' => isset($conf['format'])
          ? $conf['format']
          : 'hidden',
        '#attributes' => ['class' => ['field-formatter-type']],
      ];
    }
    else {
      $form['format'] = [
        '#type' => 'value',
        '#value' => 'default',
      ];
    }

    $form['formatter_settings'] = module_invoke(
      $this->field['module'],
      'ds_field_settings_form',
      $this->field);

    return $form;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *   An object that controls the format of the summary.
   *
   * @return mixed|string|null
   *   A string summary is always allowed. But other values may be returned if
   *   $summaryBuilder generates them.
   */
  public function confGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {
    return NULL;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   *
   * @return mixed
   *   Value to be used in the application.
   */
  public function confGetValue($conf) {
    return $conf;
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
    return $helper->export($conf);
  }
}
