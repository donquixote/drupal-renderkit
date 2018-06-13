<?php

namespace Drupal\renderkit\EntityImage;

use Drupal\renderkit\StaticHubBase;

class EntityImage extends StaticHubBase {

  const INTERFACE_NAME = EntityImageInterface::class;

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf) {

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof EntityImageInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
