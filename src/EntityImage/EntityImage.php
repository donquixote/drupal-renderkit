<?php

namespace Drupal\renderkit\EntityImage;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class EntityImage extends StaticHubBase {

  const INTERFACE_NAME = EntityImageInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\EntityImage\EntityImageInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof EntityImageInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
