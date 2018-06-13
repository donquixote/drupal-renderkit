<?php

namespace Drupal\renderkit\EntityBuildProcessor;

use Drupal\renderkit\StaticHubBase;

class EntityBuildProcessor extends StaticHubBase {

  const INTERFACE_NAME = EntityBuildProcessorInterface::class;

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf) {

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof EntityBuildProcessorInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
