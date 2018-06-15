<?php

namespace Drupal\renderkit\ListFormat;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class ListFormat extends StaticHubBase {

  const INTERFACE_NAME = ListFormatInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\ListFormat\ListFormatInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof ListFormatInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
