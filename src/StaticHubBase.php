<?php

namespace Drupal\renderkit;

use Drupal\cfrapi\Configurator\Sequence\Configurator_SequenceTabledrag;
use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrapi\Exception\ConfToValueException;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilder_Static;
use Drupal\renderkit\Configurator\Configurator_Passthru;

abstract class StaticHubBase {

  const INTERFACE_NAME = '?';

  /**
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function configurator(CfrContextInterface $context = NULL) {
    return cfrplugin()->interfaceGetConfigurator(
      static::INTERFACE_NAME,
      $context);
  }

  /**
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\Optional\OptionalConfiguratorInterface
   */
  public static function optionalConfigurator(CfrContextInterface $context = NULL) {
    return cfrplugin()->interfaceGetOptionalConfigurator(
      static::INTERFACE_NAME,
      $context);
  }

  /**
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function sequenceConfigurator(CfrContextInterface $context = NULL) {
    return new Configurator_SequenceTabledrag(
      static::configurator($context));
  }

  /**
   * @param \Drupal\cfrapi\Context\CfrContextInterface|null $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function passthruConfigurator(CfrContextInterface $context = NULL) {
    return new Configurator_Passthru(
      static::configurator($context));
  }

  /**
   * @param mixed $conf
   *
   * @return mixed|null|string
   */
  public static function summary($conf) {
    return static::configurator()->confGetSummary(
      $conf,
      new SummaryBuilder_Static());
  }

  /**
   * This should be overridden for IDE
   *
   * @param mixed $conf
   *
   * @return object
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   * @throws \RuntimeException
   */
  public static function fromConf($conf) {

    $interface = self::INTERFACE_NAME;

    if ('?' === $interface) {

      if (self::class === static::class) {
        $method = __METHOD__;
        throw new \RuntimeException("Method $method must only be called on child classes!");
      }

      $class = self::class;
      $childClass = static::class;
      throw new \RuntimeException("The $class::INTERFACE_NAME constant must be overridden in $childClass!");
    }

    $candidate = self::configurator()->confGetValue($conf);

    if ($candidate instanceof $interface) {
      return $candidate;
    }

    throw self::unexpectedValueException($candidate);
  }

  /**
   * @param mixed $candidate
   *
   * @return \Drupal\cfrapi\Exception\ConfToValueException
   */
  public static function unexpectedValueException($candidate) {

    $interface = self::INTERFACE_NAME;

    if (!is_object($candidate)) {
      $type = gettype($candidate);
      return new ConfToValueException(
        "The configurator is expected to return a $interface object, $type value found instead.");
    }

    if ($candidate instanceof $interface) {
      return new ConfToValueException(
        "The configurator returned a $interface object, but somebody is still unhappy.");
    }

    $class = get_class($candidate);
    return new ConfToValueException(
      "The configurator is expected to return a $interface object, $class object found instead.");
  }

}
