<?php

namespace Drupal\renderkit\EntityDisplay\Images;

use Drupal\renderkit\EntityDisplay\EntityDisplayBase;
use Drupal\renderkit\EntityImages\EntityImagesInterface;
use Drupal\renderkit\ImagesDisplay\ImagesDisplayInterface;

/**
 * @CfrPlugin(
 *   id = "images",
 *   label = "Images"
 * )
 */
class EntityDisplay_Images extends EntityDisplayBase {

  /**
   * @var \Drupal\renderkit\EntityImages\EntityImagesInterface
   */
  private $entityImages;

  /**
   * @var \Drupal\renderkit\ImagesDisplay\ImagesDisplayInterface
   */
  private $imagesDisplay;

  /**
   * @param \Drupal\renderkit\EntityImages\EntityImagesInterface $entityImages
   * @param \Drupal\renderkit\ImagesDisplay\ImagesDisplayInterface $imagesDisplay
   */
  public function __construct(EntityImagesInterface $entityImages, ImagesDisplayInterface $imagesDisplay) {
    $this->entityImages = $entityImages;
    $this->imagesDisplay = $imagesDisplay;
  }

  /**
   * Same as ->buildEntities(), just for a single entity.
   *
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
    $images = $this->entityImages->entityGetImages($entity_type, $entity);
    if ($images === []) {
      return [];
    }
    if (!is_array($images)) {
      $class = get_class($this->entityImages);
      throw new \RuntimeException("Injected {$class}->entityGetImages() component did not return an array.");
    }
    return $this->imagesDisplay->buildImages($images);
  }
}
