<?php

namespace Drupal\renderkit\ImageProcessor;

use Drupal\renderkit\EnumMap\EnumMap_ImageStyle;
use Drupal\cfrapi\Configurator\Id\Configurator_LegendSelect;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;

class ImageProcessor_ImageStyle implements ImageProcessorInterface {

  /**
   * @var string
   */
  private $styleName;

  /**
   * @CfrPlugin(
   *   id = "imageStyle",
   *   label = @t("Image style")
   * )
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  static function createConfigurator() {
    $imageStyleMap = new EnumMap_ImageStyle();
    $paramConfigurators = array(Configurator_LegendSelect::createRequired($imageStyleMap));
    $labels = array(t('Image style'));
    return Configurator_CallbackConfigurable::createFromClassName(__CLASS__, $paramConfigurators, $labels);
  }

  /**
   * @param string $styleName
   *   The image style name.
   */
  function __construct($styleName) {
    $this->styleName = $styleName;
  }

  /**
   * @param array $build
   *   Render array with '#theme' => 'image'.
   *
   * @return array
   *   Render array after the processing.
   */
  function processImage(array $build) {
    if (empty($build)) {
      return array();
    }
    /* @see theme_image_style() */
    $build['#theme'] = 'image_style';
    $build['#style_name'] = $this->styleName;
    return $build;
  }
}