<?php

namespace Drupal\renderkit\ImageDerivative;

class ImageResponsive implements ImageDerivativeInterface {

  /**
   * The fallback style name.
   *
   * @var string
   */
  protected $fallbackStyleName;

  /**
   * @var string[]
   */
  protected $sizes = array();

  /**
   * @var string[]
   */
  protected $styleNames = array();

  /**
   * @param string|null $fallbackStyleName
   *   Image style for the fallback image (src attribute), for browsers that
   *   don't understand srcset.
   */
  function __construct($fallbackStyleName = NULL) {
    $this->fallbackStyleName = $fallbackStyleName;
    $this->styleNames[] = $fallbackStyleName;
  }

  /**
   * @param int $min_width_px
   * @param string $formula
   *
   * @return $this
   */
  function minWidthSize($min_width_px, $formula) {
    if ($min_width_px > 0) {
      $media_query = 'min-width: ' . $min_width_px . 'px';
      $formula = '(' . $media_query . ') ' . $formula;
    }
    $this->sizes[$min_width_px] = $formula;
    return $this;
  }

  /**
   * @param int $min_width_px
   * @param int|float $ratio
   * @param int|float $space
   *
   * @return $this
   */
  function minWidthColumn($min_width_px, $ratio, $space) {
    $percentage = (100 * $ratio) . 'vw';
    $subtract = ($space * $ratio) . 'px';
    $formula = 'calc(' . $percentage . ' - ' . $subtract . ')';
    $this->minWidthSize($min_width_px, $formula);
    return $this;
  }

  /**
   * @param string $style_name
   *
   * @return $this
   */
  function addImageStyle($style_name) {
    $this->styleNames[] = $style_name;
    return $this;
  }

  /**
   * @param array $build
   *   Render array with '#theme' => 'image'.
   *
   * @return array
   *   Render array after the processing.
   */
  function processImage(array $build) {

    // Path to the original image file.
    $original_path = $build['#path'];
    $original_dimensions = $this->imageBuildGetDimensions($build);

    if (!empty($this->fallbackStyleName)) {
      /* @see theme_image_style() */
      $style_dimensions = $original_dimensions;
      image_style_transform_dimensions($this->fallbackStyleName, $style_dimensions);
      $build['#path'] = image_style_url($this->fallbackStyleName, $original_path);
      $build['#width'] = $style_dimensions['width'];
      $build['#height'] = $style_dimensions['height'];
    }

    if (empty($this->sizes) || empty($this->styleNames)) {
      return $build;
    }

    /* @see theme_picture() */
    /* @see theme_image_srcset() */
    $srcset = array();
    foreach ($this->styleNames as $style_name) {
      $style_src = image_style_url($style_name, $original_path);
      $style_dimensions = $original_dimensions;
      image_style_transform_dimensions($style_name, $style_dimensions);
      $srcset[] = $style_src . ' ' . $style_dimensions['width'] . 'w';
    }
    $build['#attributes']['srcset'] = implode(', ', $srcset);

    krsort($this->sizes);
    $build['#attributes']['sizes'] = empty($this->sizes)
      ? '100vw'
      : implode(', ', $this->sizes);

    return $build;
  }

  /**
   * @param array $build
   *   A render array with '#theme' => 'image'.
   *
   * @return int[]
   *   Format: array('width' => $width, 'height' => $height)
   * @throws \Exception
   */
  protected function imageBuildGetDimensions(array $build) {
    if (!empty($build['#width']) && !empty($build['#height'])) {
      return array(
        'width' => $build['#width'],
        'height' => $build['#height'],
      );
    }
    $image_info = image_get_info($build['#path']);
    if (FALSE === $image_info) {
      throw new \Exception("Unable to load image.");
    }
    return array(
      'width' => $image_info['width'],
      'height' => $image_info['height'],
    );
  }

}
