<?php

namespace Drupal\renderkit\Util;

final class RenderUtil extends UtilBase {

  /**
   * @param array $images
   */
  public static function validateImages(array $images) {
    self::validateList($images);
    foreach ($images as $delta => $image) {
      self::validateImage($image);
    }
  }

  /**
   * @param array $image
   */
  public static function validateImage(array $image) {
    if (!array_key_exists('#theme', $image)) {
      throw new \RuntimeException("\$image['#theme'] is not set.");
    }
    if ('image' !== $image['#theme']) {
      throw new \RuntimeException("\$image['#theme'] !== 'image'.");
    }
  }

  /**
   * @param array $items
   */
  public static function validateList(array $items) {
    foreach ($items as $delta => $item) {
      if ('#' === $delta[0]) {
        throw new \RuntimeException("Illegal delta '$delta'.");
      }
      if (!\is_array($item)) {
        $item_export = var_export($item, TRUE);
        throw new \RuntimeException("Item at delta '$delta' is not an array, but $item_export.");
      }
    }
  }

}
