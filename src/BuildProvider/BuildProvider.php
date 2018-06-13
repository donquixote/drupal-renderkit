<?php

namespace Drupal\renderkit\BuildProvider;

use Drupal\renderkit\StaticHubBase;

class BuildProvider extends StaticHubBase {

  const INTERFACE_NAME = BuildProviderInterface::class;

  /**
   * @param mixed $conf
   *
   * @return \Drupal\renderkit\BuildProvider\BuildProviderInterface
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf) {

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof BuildProviderInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
