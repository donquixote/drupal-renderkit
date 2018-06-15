<?php

namespace Drupal\renderkit\EntityImages;

use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\Configurator\Id\Configurator_FieldName;

class EntityImages_ImageField implements EntityImagesInterface {

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @CfrPlugin(
   *   id = "imageField",
   *   label = "Image field"
   * )
   *
   * @param string $entityType
   *   (optional) Contextual parameter.
   * @param string $bundleName
   *   (optional) Contextual parameter.
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createPlugin($entityType = NULL, $bundleName = NULL) {
    $configurators = [new Configurator_FieldName(['image'], $entityType, $bundleName)];
    $labels = [t('Image field')];
    return Configurator_CallbackConfigurable::createFromClassName(__CLASS__, $configurators, $labels);
  }

  /**
   * @param string $fieldName
   *   The name of an image field, e.g. 'field_teaser_image'.
   */
  public function __construct($fieldName) {
    $this->fieldName = $fieldName;
  }

  /**
   * @param string $entity_type
   * @param object $entity
   *
   * @return array
   *   Render array for one entity.
   */
  public function entityGetImages($entity_type, $entity) {
    $items = field_get_items($entity_type, $entity, $this->fieldName) ?: [];
    $builds = [];
    foreach ($items as $delta => $item) {
      $builds[$delta] = $this->buildFieldItem($item);
    }
    return $builds;
  }

  /**
   * @param array $item
   *   Field item from an image field.
   *
   * @return array
   *
   * @see theme_image_formatter()
   */
  protected function buildFieldItem(array $item) {

    $build = [
      '#theme' => 'image',
      '#path' => $item['uri'],
    ];

    if (array_key_exists('alt', $item)) {
      $build['#alt'] = $item['alt'];
    }

    if (isset($item['attributes'])) {
      $build['#attributes'] = $item['attributes'];
    }

    if (isset($item['width'], $item['height'])) {
      $build['#width'] = $item['width'];
      $build['#height'] = $item['height'];
    }

    // Do not output an empty 'title' attribute.
    if (isset($item['title']) && drupal_strlen($item['title']) > 0) {
      $build['#title'] = $item['title'];
    }

    return $build;
  }
}
