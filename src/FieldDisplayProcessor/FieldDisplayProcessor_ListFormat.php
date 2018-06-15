<?php

namespace Drupal\renderkit\FieldDisplayProcessor;

use Drupal\cfrapi\Configurator\Bool\Configurator_Checkbox;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\ListFormat\ListFormat;
use Drupal\renderkit\ListFormat\ListFormatInterface;

class FieldDisplayProcessor_ListFormat implements FieldDisplayProcessorInterface {

  /**
   * @var \Drupal\renderkit\ListFormat\ListFormatInterface
   */
  private $listFormat;

  /**
   * @CfrPlugin("listFormatPlus", "List format +")
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator() {
    return Configurator_CallbackConfigurable::createFromClassStaticMethod(
      __CLASS__,
      'create',
      [
        ListFormat::configurator(),
        new Configurator_Checkbox(),
      ],
      [
        t('List format'),
        t('Add field classes'),
      ]);
  }

  /**
   * @param \Drupal\renderkit\ListFormat\ListFormatInterface $listFormat
   * @param bool $withFieldClasses
   *
   * @return \Drupal\renderkit\FieldDisplayProcessor\FieldDisplayProcessorInterface
   */
  public static function create(ListFormatInterface $listFormat, $withFieldClasses = FALSE) {
    $fieldDisplayProcessor = new self($listFormat);
    if ($withFieldClasses) {
      $fieldDisplayProcessor = new FieldDisplayProcessor_FieldClasses($fieldDisplayProcessor);
    }
    return $fieldDisplayProcessor;
  }

  /**
   * @param \Drupal\renderkit\ListFormat\ListFormatInterface $listFormat
   */
  public function __construct(ListFormatInterface $listFormat) {
    $this->listFormat = $listFormat;
  }

  /**
   * @param array $element
   *   Render array with ['#theme' => 'field', ..]
   *
   * @return array
   */
  public function process(array $element) {

    $builds = [];
    foreach ($element['#items'] as $delta => $item) {
      if (!empty($element[$delta])) {
        $builds[$delta] = $element[$delta];
      }
    }

    return $this->listFormat->buildList($builds);
  }
}
