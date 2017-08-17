<?php

namespace Drupal\renderkit8\EntityImages;

use Donquixote\Cf\Schema\Boolean\CfSchema_Boolean_YesNo;
use Donquixote\Cf\Schema\GroupVal\CfSchema_GroupVal_Callback;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\file\Plugin\Field\FieldType\FileFieldItemList;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\renderkit8\Schema\CfSchema_EtDotFieldName_AllowedTypes;
use Drupal\renderkit8\Util\ImageFieldUtil;

class EntityImages_ImageField implements EntityImagesInterface {

  /**
   * @var string
   */
  private $entityTypeId;

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @var bool
   */
  private $useDefaultImage;

  /**
   * @CfrPlugin(
   *   id = "imageField",
   *   label = "Image field"
   * )
   *
   * @param string $entityType
   * @param string $bundleName
   *
   * @return \Donquixote\Cf\Schema\GroupVal\CfSchema_GroupValInterface
   */
  public static function createSchema($entityType = NULL, $bundleName = NULL) {

    return CfSchema_GroupVal_Callback::fromStaticMethod(
      __CLASS__,
      'create',
      [
        new CfSchema_EtDotFieldName_AllowedTypes(
          $entityType,
          $bundleName,
          ['image']),
        CfSchema_Boolean_YesNo::create(TRUE),
      ],
      [
        t('Image field'),
        t('Use default image as fallback'),
      ]);
  }

  /**
   * @param string $etDotFieldName
   * @param bool $useDefaultImage
   *
   * @return self
   */
  public static function create($etDotFieldName, $useDefaultImage = TRUE) {

    list($entityTypeId, $fieldName) = explode('.', $etDotFieldName) + [NULL, NULL];

    if (NULL === $fieldName || '' === $entityTypeId || '' === $fieldName) {
      return NULL;
    }

    return new self(
      $entityTypeId,
      $fieldName,
      $useDefaultImage);
  }

  /**
   * @param string $entityTypeId
   * @param string $fieldName
   * @param bool $useDefaultImage
   */
  public function __construct($entityTypeId, $fieldName, $useDefaultImage = TRUE) {
    $this->entityTypeId = $entityTypeId;
    $this->fieldName = $fieldName;
    $this->useDefaultImage = $useDefaultImage;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   *   Render array for one entity.
   */
  public function entityGetImages(EntityInterface $entity) {

    if ($this->entityTypeId !== $entity->getEntityTypeId()) {
      return [];
    }

    if (!$entity instanceof FieldableEntityInterface) {
      return [];
    }

    $items = $entity->get($this->fieldName);

    if (!$items instanceof FileFieldItemList) {
      return [];
    }

    if ($this->useDefaultImage) {
      $items = ImageFieldUtil::itemsGetItems($items);
    }

    $builds = [];
    foreach ($items as $delta => $item) {

      if (!$item instanceof ImageItem) {
        continue;
      }

      $builds[$delta] = ImageFieldUtil::buildImageFieldItem($item);
    }

    return $builds;
  }
}
