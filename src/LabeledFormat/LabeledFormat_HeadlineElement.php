<?php

namespace Drupal\renderkit\LabeledFormat;

use Drupal\cfrapi\Configurator\Id\Configurator_FlatOptionsSelect;
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
  public static function createConfigurator() {
    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      [
        new Configurator_FlatOptionsSelect(
          [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
          ]
        ),
      ],
      [
        t('Label element tag name'),
      ]
    );
  }

  /**
   * @param string $tagName
   */
  public function __construct($tagName = 'h2') {
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
  public function buildAddLabel(array $build, $label) {
    $label_markup = '<' . $this->tagName . '>' . check_plain($label) . '</' . $this->tagName . '>';
    return [
      'label' => [
        '#markup' => $label_markup,
      ],
      'contents' => $build,
    ];
  }
}
