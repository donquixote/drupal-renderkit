<?php

namespace Drupal\renderkit\LabeledFormat;

use Drupal\cfrapi\Configurator\Id\Configurator_LegendSelect;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;

class LabeledFormat_HeadlineElement implements LabeledFormatInterface {

  /**
   * @var string
   */
  private $tagName;

  /**
   * @CfrPlugin(
   *   id = "headlineElement",
   *   label = "Headline element"
   * )
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  static function createConfigurator() {
    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      array(
        Configurator_LegendSelect::createFromOptions(
          array(
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
          )),
      ),
      array(
        t('Label element tag name'),
      ));
  }

  /**
   * @param string $tagName
   */
  function __construct($tagName = 'h2') {
    $this->tagName = $tagName;
  }

  /**
   * @param array $build
   *   Original render array (without label).
   * @param string $label
   *   A label / title.
   *
   * @return array
   *   Modified or wrapped render array with label.
   */
  function buildAddLabel(array $build, $label) {
    $label_markup = '<h2>' . check_plain($label) . '</h2>';
    return array(
      'label' => array(
        '#markup' => $label_markup,
      ),
      'contents' => $build,
    );
  }
}