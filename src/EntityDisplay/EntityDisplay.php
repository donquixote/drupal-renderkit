<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\renderkit\StaticHubBase;

class EntityDisplay extends StaticHubBase {

  const INTERFACE_NAME = EntityDisplayInterface::class;

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf) {

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof EntityDisplayInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
