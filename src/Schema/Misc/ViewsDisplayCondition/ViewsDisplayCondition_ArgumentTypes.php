<?php

namespace Drupal\renderkit8\Schema\Misc\ViewsDisplayCondition;

class ViewsDisplayCondition_ArgumentTypes implements ViewsDisplayConditionInterface {

  /**
   * @var string[]
   */
  private $argumentsSignature;

  /**
   * @param string[] $argumentsSignature
   */
  public function __construct(array $argumentsSignature) {
    $this->argumentsSignature = $argumentsSignature;
  }

  /**
   * @param string $id
   * @param array $display
   * @param array|null $default_display
   *
   * @return bool
   */
  public function displayCheckCondition($id, array $display, array $default_display = NULL) {

    if (isset($display['display_options']['arguments'])) {
      $arguments = $display['display_options']['arguments'];
    }
    elseif (isset($default_display['display_options']['arguments'])) {
      $arguments = $default_display['display_options']['arguments'];
    }
    else {
      $arguments = [];
    }

    if ([] === $this->argumentsSignature) {
      return [] === $arguments;
    }

    $argTypes = [];
    foreach ($arguments as $argument) {
      if (isset($argument['validate']['type'])) {
        $argTypes[] = $argument['validate']['type'];
      }
      else {
        $argTypes[] = NULL;
      }
    }

    return $this->argumentsSignature === $argTypes;
  }
}
