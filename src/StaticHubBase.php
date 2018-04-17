<?php

namespace Drupal\renderkit;

use Drupal\cfrapi\Configurator\Sequence\Configurator_Sequence;
use Drupal\cfrapi\Context\CfrContextInterface;
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
    return new Configurator_Sequence(
      static::optionalConfigurator($context));
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
   * This should be overridden!
   *
   * @param mixed $conf
   *
   * @throws \RuntimeException
   */
  public static function fromConf($conf) {

    $method = static::class . '::' . __FUNCTION__ . '()';

    throw new \RuntimeException(
      "Method $method does not exist.");
  }

}
