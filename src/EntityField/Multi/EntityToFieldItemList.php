<?php

namespace Drupal\renderkit8\EntityField\Multi;

use Donquixote\Cf\Schema\CfSchema;
use Drupal\renderkit8\Context\FieldContext;
use Drupal\renderkit8\Util\UtilBase;

final class EntityToFieldItemList extends UtilBase {

  /**
   * @param string[]|null $allowedFieldTypes
   * @param string|null $entityType
   * @param string|null $bundle
   *
   * @return \Donquixote\Cf\Schema\CfSchemaInterface
   */
  public static function schema(
    array $allowedFieldTypes = NULL,
    $entityType = NULL,
    $bundle = NULL
  ) {
    return CfSchema::iface(
      EntityToFieldItemListInterface::class,
      FieldContext::get(
        $allowedFieldTypes,
        $entityType,
        $bundle));
  }

}
