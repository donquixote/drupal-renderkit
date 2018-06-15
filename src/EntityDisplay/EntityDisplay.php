<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class EntityDisplay extends StaticHubBase {

  const INTERFACE_NAME = EntityDisplayInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof EntityDisplayInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
