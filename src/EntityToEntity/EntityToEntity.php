<?php

namespace Drupal\renderkit\EntityToEntity;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class EntityToEntity extends StaticHubBase {

  const INTERFACE_NAME = EntityToEntityInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\EntityToEntity\EntityToEntityInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof EntityToEntityInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
