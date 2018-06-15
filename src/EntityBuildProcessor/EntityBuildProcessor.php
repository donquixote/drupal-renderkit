<?php

namespace Drupal\renderkit\EntityBuildProcessor;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\renderkit\StaticHubBase;

class EntityBuildProcessor extends StaticHubBase {

  const INTERFACE_NAME = EntityBuildProcessorInterface::class;

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\renderkit\EntityBuildProcessor\EntityBuildProcessorInterface
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function fromConf($conf, CfrContextInterface $context = NULL) {

    $candidate = self::configurator($context)->confGetValue($conf);

    if ($candidate instanceof EntityBuildProcessorInterface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

}
