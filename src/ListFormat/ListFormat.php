<?php

namespace Drupal\renderkit\ListFormat;

use Drupal\renderkit\StaticHubBase;

class ListFormat extends StaticHubBase {

  const INTERFACE_NAME = ListFormatInterface::class;

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf) {

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof ListFormatInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
