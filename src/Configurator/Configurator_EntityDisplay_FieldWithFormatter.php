<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\Group\Configurator_GroupBase;
use Drupal\cfrapi\Configurator\Id\Configurator_FlatOptionsSelect;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;
use Drupal\renderkit\EntityDisplay\EntityDisplay_FieldWithFormatter;
use Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface;

class Configurator_EntityDisplay_FieldWithFormatter extends Configurator_GroupBase {

  /**
   * @param string|null $entityType
   * @param string|null $bundleName
   */
  public function __construct($entityType = NULL, $bundleName = NULL) {

    $this->keySetConfigurator(
      'field',
      new Configurator_FieldNameWithFormatter($entityType, $bundleName),
      t('Field'));

    $this->keySetConfigurator(
      'label',
      Configurator_FlatOptionsSelect::createRequired(
        [
          'above' => t('Above'),
          'inline' => t('Inline'),
          'hidden' => '<' . t('Hidden') . '>',
        ],
        'hidden'),
      t('Label display'));

    $this->keySetConfigurator(
      'processor',
      cfrplugin()->interfaceGetOptionalConfigurator(FieldDisplayProcessorInterface::class),
      t('Field display processor'));
  }

  /**
   * @param mixed $conf
   * @param string $label
   *
   * @return array
   */
  public function confGetForm($conf, $label) {
    $conf = $this->confGetNormalized($conf);
    return parent::confGetForm($conf, $label);
  }

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *
   * @return null|string
   */
  public function confGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {
    $conf = $this->confGetNormalized($conf);
    return parent::confGetSummary($conf, $summaryBuilder);
  }

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface|mixed
   */
  public function confGetValue($conf) {
    $conf = $this->confGetNormalized($conf);

    $value = parent::confGetValue($conf);

    if (!is_array($value)) {
      return $value;
    }

    $fieldName = $value['field']['field'];
    $display = $value['field']['display'];
    $display['label'] = $value['label'];

    return new EntityDisplay_FieldWithFormatter($fieldName, $display, $value['processor']);
  }

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement to generate the value.
   *
   * @throws \Drupal\cfrapi\Exception\InvalidConfigurationException
   */
  public function confGetPhp($conf, CfrCodegenHelperInterface $helper) {
    $conf = $this->confGetNormalized($conf);

    try {
      $group_values = parent::confGetValue($conf);
    }
    catch (\Exception $e) {
      return $helper->incompatibleConfiguration($conf, $e->getMessage());
    }

    $group_snippets = parent::confGetPhpStatements($conf, $helper);

    if (!is_array($group_values)) {
      return $helper->incompatibleConfiguration($conf, "Invalid field settings.");
    }

    $fieldName = $group_values['field']['field'];
    $display = $group_values['field']['display'];
    $display['label'] = $group_values['label'];

    return 'new ' . EntityDisplay_FieldWithFormatter::class . '('
      . "\n  " . var_export($fieldName, TRUE) . ','
      . "\n  " . $helper->export($display) . ','
      . "\n  " . $group_snippets['processor'] . ')';
  }

  /**
   * Updates legacy configurations saved with earlier versions of this plugin.
   *
   * @param mixed $conf
   *
   * @return mixed
   */
  private function confGetNormalized($conf) {

    if (!is_array($conf) || !isset($conf['field']) || !is_string($conf['field'])) {
      return $conf;
    }

    $normalized = [];
    $normalized['field']['field'] = $conf['field'];

    if (isset($conf['display']) && is_array($conf['display'])) {
      $display = $conf['display'];
      if (isset($display['label'])) {
        $normalized['label'] = $display['label'];
        unset($display['label']);
      }
      $normalized['field']['display'] = $display;
    }

    if (isset($conf['processor'])) {
      $normalized['processor'] = $conf['processor'];
    }

    return $normalized;
  }

}
