<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Exception\ConfToValueException;
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

    throw new ConfToValueException(
      "The configurator returned something other than a batch operation object.");
  }

}
