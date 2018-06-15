<?php

namespace Drupal\renderkit\BuildProvider;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class BuildProvider extends StaticHubBase {

  const INTERFACE_NAME = BuildProviderInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\BuildProvider\BuildProviderInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof BuildProviderInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
