<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\Configurator\Configurator_DecoratorBase;
use Drupal\cfrapi\Configurator\ConfiguratorInterface;

class Configurator_Passthru extends Configurator_DecoratorBase {

  /**
   * @param \Drupal\cfrapi\Configurator\ConfiguratorInterface $decorated
   */
  public function __construct(ConfiguratorInterface $decorated) {
    parent::__construct($decorated);
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   *
   * @return mixed
   *   Value to be used in the application.
   */
  public function confGetValue($conf) {
    return $conf;
  }

}
